<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * QikinkService
 * Integrates Qikink Print on Demand (POD) & Dropshipping API.
 * Handles JWT Access Token generation and caching, order submission,
 * tracking, rate limiting, and sandbox/live endpoints.
 */
class QikinkService
{
    private const SANDBOX_URL = 'https://sandbox.qikink.com';
    private const LIVE_URL    = 'https://api.qikink.com';
    
    private const TOKEN_CACHE = 'qikink_access_token';
    private const TOKEN_TTL   = 3300; // 55 minutes (token expires in 1 hour)

    /**
     * Get the base URL based on sandbox mode setting
     */
    public function getBaseUrl(): string
    {
        $sandbox = Setting::getSetting('qikink_sandbox_mode', '1');
        return $sandbox === '1' ? self::SANDBOX_URL : self::LIVE_URL;
    }

    /**
     * Get ClientId from settings
     */
    public function getClientId(): string
    {
        return Setting::getSetting('qikink_client_id', config('services.qikink.client_id', ''));
    }

    /**
     * Get client_secret from settings
     */
    public function getClientSecret(): string
    {
        return Setting::getSetting('qikink_client_secret', config('services.qikink.client_secret', ''));
    }

    /**
     * Get a valid access token from Cache or API
     */
    public function getAccessToken(): string
    {
        return Cache::remember(self::TOKEN_CACHE, self::TOKEN_TTL, function () {
            return $this->fetchFreshToken();
        });
    }

    /**
     * Fetch a fresh AccessToken from Qikink API
     */
    private function fetchFreshToken(): string
    {
        $clientId     = $this->getClientId();
        $clientSecret = $this->getClientSecret();

        if (empty($clientId) || empty($clientSecret)) {
            Log::error('Qikink Service: Credentials not configured');
            throw new \RuntimeException('Qikink ClientId or client_secret are not configured in settings or .env');
        }

        $url = $this->getBaseUrl() . '/api/token';
        
        Log::info('Qikink Service: Fetching fresh access token', [
            'url'           => $url,
            'client_id'     => $this->maskString($clientId),
            'client_secret' => $this->maskString($clientSecret),
        ]);
        
        $response = Http::asForm()->post($url, [
            'ClientId'      => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if (!$response->successful() || empty($response->json('Accesstoken'))) {
            Log::error('Qikink token fetch failed', ['body' => $response->body()]);
            throw new \RuntimeException('Qikink Authentication failed: ' . ($response->json('message') ?? $response->body()));
        }

        return $response->json('Accesstoken');
    }

    /**
     * Helper to mask sensitive credentials for logs
     */
    private function maskString(string $str): string
    {
        $len = strlen($str);
        if ($len <= 6) {
            return str_repeat('*', $len);
        }
        return substr($str, 0, 3) . '...' . substr($str, -3);
    }

    /**
     * Force refresh cached token
     */
    public function refreshToken(): string
    {
        Cache::forget(self::TOKEN_CACHE);
        return $this->getAccessToken();
    }

    /**
     * Build an authenticated HTTP client
     */
    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'ClientId'    => $this->getClientId(),
            'Accesstoken' => $this->getAccessToken(),
            'Accept'      => 'application/json',
        ]);
    }

    /**
     * Execute an API call, with retry logic for 401s and rate limits (429)
     */
    private function call(string $method, string $endpoint, array $payload = []): array
    {
        $url = $this->getBaseUrl() . $endpoint;
        
        $execute = function() use ($method, $url, $payload) {
            if ($method === 'GET') {
                return $this->http()->get($url, $payload);
            }
            return $this->http()->post($url, $payload);
        };

        $response = $execute();

        // 1. If unauthorized (401), refresh token and retry once
        if ($response->status() === 401 || $response->json('error') === 'Invalid token') {
            Log::warning('Qikink 401/Invalid Token: Refreshing access token and retrying...');
            $this->refreshToken();
            $response = $execute();
        }

        // 2. If rate limited (30 requests/min), wait 2 seconds and retry once
        if ($response->status() === 429 || str_contains($response->json('error') ?? '', 'Rate limit exceeded')) {
            Log::warning('Qikink 429 Rate Limit Exceeded: Throttling request...');
            sleep(2);
            $response = $execute();
        }

        if (!$response->successful()) {
            Log::error("Qikink API Error on {$url}", [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);
            throw new \RuntimeException("Qikink API Error: Status {$response->status()} - {$response->body()}");
        }

        return $response->json() ?? [];
    }

    /**
     * Test credentials and connection to Qikink
     */
    public function testConnection(): array
    {
        try {
            $token = $this->refreshToken(); // force fresh auth check
            return [
                'success' => true,
                'message' => 'Credentials verified successfully.',
                'token'   => substr($token, 0, 8) . '...'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create an order in Qikink
     */
    public function createOrder(array $orderData): array
    {
        Log::info('Qikink Service: Creating order ' . ($orderData['order_number'] ?? ''));
        return $this->call('POST', '/api/order/create', $orderData);
    }

    /**
     * Get order details by Qikink order ID or client order number
     */
    public function getOrder(string $id): array
    {
        return $this->call('GET', '/api/order', ['id' => $id]);
    }

    /**
     * Sync order status from Qikink API list
     */
    public function getOrdersList(array $filters = []): array
    {
        return $this->call('GET', '/api/order', $filters);
    }

    /**
     * Map a local order model to Qikink API format
     */
    public function mapOrderData(\App\Models\Order $order): array
    {
        $lineItems = [];
        $defaultPrintTypeId = Setting::getSetting('qikink_default_print_type_id', '1'); // 1 = DTG
        $defaultShipping = Setting::getSetting('qikink_default_shipping', '1'); // 1 = Qikink shipping

        foreach ($order->items as $item) {
            // Check if product is Qikink fulfilled
            $productId = $item['product_id'] ?? ($item['id'] ?? null);
            $product = \App\Models\Products::find($productId);

            if (!$product || !$product->is_qikink_product) {
                continue; // Skip non-Qikink items (e.g. shiprocket-fulfilled items)
            }

            // SKU matching logic
            $sku = $product->qikink_sku ?: ($item['sku'] ?? $product->slug);
            // Append color and size to SKU if not already present
            if (isset($item['size']) && !str_ends_with($sku, '-' . $item['size'])) {
                $sku .= '-' . $item['size'];
            }

            $searchFromMyProducts = $product->search_from_my_products ? 1 : 0;
            $printTypeId = $product->qikink_print_type_id ?: $defaultPrintTypeId;

            $mappedItem = [
                'search_from_my_products' => $searchFromMyProducts,
                'quantity'                => (string) ($item['quantity'] ?? 1),
                'price'                   => (string) ($item['price'] ?? 0),
                'sku'                     => $sku,
            ];

            if ($searchFromMyProducts === 0) {
                $mappedItem['print_type_id'] = (int) $printTypeId;
                
                // Fetch design links stored from custom designer upload
                $designUrl = $item['custom_design_url'] ?? null;
                $mockupUrl = $item['custom_preview_url'] ?? null;

                if (empty($designUrl)) {
                    // Fallback to product images if it is a pre-designed catalog item but sent via design_code
                    $designUrl = $product->main_image ? asset('storage/' . $product->main_image) : '';
                }

                if (empty($mockupUrl)) {
                    $mockupUrl = $product->main_image ? asset('storage/' . $product->main_image) : '';
                }

                // Check design code, if missing generate one based on product name/ID
                $designCode = $item['design_code'] ?? 'DSN-' . $product->id . '-' . strtoupper(substr(md5($product->name), 0, 6));

                $mappedItem['designs'] = [
                    [
                        'design_code'   => $designCode,
                        'width_inches'  => "12",  // default print dimensions
                        'height_inches' => "16",  // default print dimensions
                        'placement_sku' => "fr",  // Default Front
                        'design_link'   => $designUrl,
                        'mockup_link'   => $mockupUrl
                    ]
                ];
            }

            $lineItems[] = $mappedItem;
        }

        if (empty($lineItems)) {
            throw new \InvalidArgumentException('No Qikink-fulfilled items found in this order.');
        }

        // Split name into first and last name
        $nameParts = explode(' ', trim($order->name), 2);
        $firstName = $nameParts[0] ?? '';
        $lastName  = $nameParts[1] ?? '';

        return [
            'order_number'      => $order->order_number,
            'qikink_shipping'   => (string) $defaultShipping,
            'gateway'           => $order->payment_method === 'cod' ? 'COD' : 'Prepaid',
            'total_order_value' => (string) $order->total,
            'line_items'        => $lineItems,
            'shipping_address'  => [
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'address1'     => $order->address,
                'phone'        => $order->phone,
                'email'        => $order->email,
                'city'         => $order->city,
                'zip'          => (string) $order->zip,
                'province'     => $order->state ?: 'Karnataka',
                'country_code' => 'IN'
            ]
        ];
    }
}
