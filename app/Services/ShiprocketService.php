<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * ShiprocketService
 * Full implementation of the Shiprocket V2 API.
 * Handles authentication (with JWT token caching), order creation,
 * AWB assignment, pickup generation, tracking, labels, manifests,
 * return pickups, and cancellation.
 */
class ShiprocketService
{
    private const BASE_URL    = 'https://apiv2.shiprocket.in/v1/external';
    private const TOKEN_CACHE = 'shiprocket_jwt_token';
    private const TOKEN_TTL   = 60 * 60 * 24 * 9; // 9 days (token valid 10 days)

    // ──────────────────────────────────────────────────────────────
    // Authentication
    // ──────────────────────────────────────────────────────────────

    /**
     * Get a valid JWT token — uses cache to avoid re-authenticating on every request.
     */
    public function getAuthToken(): string
    {
        return Cache::remember(self::TOKEN_CACHE, self::TOKEN_TTL, function () {
            return $this->fetchFreshToken();
        });
    }

    /**
     * Fetch a fresh JWT token from Shiprocket API.
     */
    private function fetchFreshToken(): string
    {
        $email    = config('services.shiprocket.email');
        $password = config('services.shiprocket.password');

        if (empty($email) || empty($password)) {
            throw new \RuntimeException('Shiprocket credentials are not configured in .env');
        }

        $response = Http::post(self::BASE_URL . '/auth/login', [
            'email'    => $email,
            'password' => $password,
        ]);

        if (! $response->successful() || empty($response->json('token'))) {
            Log::error('Shiprocket auth failed', ['body' => $response->body()]);
            throw new \RuntimeException('Shiprocket authentication failed: ' . $response->body());
        }

        Log::info('Shiprocket: new JWT token fetched and cached');
        return $response->json('token');
    }

    /**
     * Force-refresh the token (call after 401 responses).
     */
    public function refreshToken(): string
    {
        Cache::forget(self::TOKEN_CACHE);
        return $this->getAuthToken();
    }

    /**
     * Build an authenticated HTTP client.
     */
    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getAuthToken(),
            'Content-Type'  => 'application/json',
        ]);
    }

    /**
     * Execute an API call; retry once after refreshing token on 401.
     */
    private function call(string $method, string $endpoint, array $payload = []): array
    {
        $url      = self::BASE_URL . $endpoint;
        $response = $method === 'GET'
            ? $this->http()->get($url, $payload)
            : $this->http()->{$method}($url, $payload);

        // Auto-refresh token on 401 and retry once
        if ($response->status() === 401) {
            Log::warning('Shiprocket 401 — refreshing token and retrying');
            Cache::forget(self::TOKEN_CACHE);
            $response = $method === 'GET'
                ? $this->http()->get($url, $payload)
                : $this->http()->{$method}($url, $payload);
        }

        if (! $response->successful()) {
            Log::error("Shiprocket API error [{$method} {$endpoint}]", [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException("Shiprocket API error ({$response->status()}): " . $response->body());
        }

        return $response->json() ?? [];
    }

    // ──────────────────────────────────────────────────────────────
    // Pickup Locations
    // ──────────────────────────────────────────────────────────────

    public function getPickupLocations(): array
    {
        return $this->call('GET', '/settings/company/pickup');
    }

    // ──────────────────────────────────────────────────────────────
    // Serviceability Check
    // ──────────────────────────────────────────────────────────────

    /**
     * Check if delivery is available to a pincode and get shipping rates.
     *
     * @param  string $pickupPincode  Warehouse pincode
     * @param  string $deliveryPincode Customer pincode
     * @param  float  $weight  Total weight in kg
     * @param  int    $cod  1 = COD, 0 = Prepaid
     */
    public function checkServiceability(
        string $pickupPincode,
        string $deliveryPincode,
        float  $weight = 0.5,
        int    $cod    = 0
    ): array {
        return $this->call('GET', '/courier/serviceability', [
            'pickup_postcode'   => $pickupPincode,
            'delivery_postcode' => $deliveryPincode,
            'weight'            => $weight,
            'cod'               => $cod,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Order / Shipment Creation
    // ──────────────────────────────────────────────────────────────

    /**
     * Create a forward (outgoing) order in Shiprocket.
     * Returns the Shiprocket response array with order_id and shipment_id.
     */
    public function createOrder(array $orderData): array
    {
        Log::info('Shiprocket: creating forward order', ['order_id' => $orderData['order_id'] ?? null]);
        return $this->call('post', '/orders/create/adhoc', $orderData);
    }

    /**
     * Assign AWB code to a shipment (select courier).
     *
     * @param  int  $shipmentId  Shiprocket shipment_id
     * @param  int  $courierId   Courier company ID (from serviceability check)
     */
    public function assignAwb(int $shipmentId, int $courierId): array
    {
        return $this->call('post', '/courier/assign/awb', [
            'shipment_id' => $shipmentId,
            'courier_id'  => $courierId,
        ]);
    }

    /**
     * Auto-assign the best/cheapest AWB (let Shiprocket pick courier).
     */
    public function generateAWB(int $shipmentId, int $orderId): array
    {
        return $this->call('post', '/courier/assign/awb', [
            'shipment_id' => (string) $shipmentId,
        ]);
    }

    /**
     * Generate pickup request for a shipment.
     */
    public function generatePickup(int $shipmentId): array
    {
        return $this->call('post', '/courier/generate/pickup', [
            'shipment_id' => [$shipmentId],
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Tracking
    // ──────────────────────────────────────────────────────────────

    /**
     * Track a shipment by AWB code.
     */
    public function trackShipment(string $awbCode): array
    {
        return $this->call('GET', "/courier/track/awb/{$awbCode}");
    }

    /**
     * Track by Shiprocket shipment ID.
     */
    public function trackByShipmentId(int $shipmentId): array
    {
        return $this->call('GET', "/courier/track/shipment/{$shipmentId}");
    }

    // ──────────────────────────────────────────────────────────────
    // Labels & Manifests
    // ──────────────────────────────────────────────────────────────

    /**
     * Generate shipping label PDF for one or more shipments.
     */
    public function generateLabel(array $shipmentIds): array
    {
        return $this->call('post', '/courier/generate/label', [
            'shipment_id' => $shipmentIds,
        ]);
    }

    /**
     * Generate manifest PDF for one or more shipments.
     */
    public function generateManifest(array $shipmentIds): array
    {
        return $this->call('post', '/manifests/generate', [
            'shipment_id' => $shipmentIds,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Cancellation
    // ──────────────────────────────────────────────────────────────

    /**
     * Cancel shipments by AWB codes.
     */
    public function cancelShipment(array $awbCodes): array
    {
        return $this->call('post', '/orders/cancel', [
            'awbs' => $awbCodes,
        ]);
    }

    /**
     * Cancel a Shiprocket order by order ID.
     */
    public function cancelOrder(int $shiprocketOrderId): array
    {
        return $this->call('post', '/orders/cancel', [
            'ids' => [$shiprocketOrderId],
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Return Pickup
    // ──────────────────────────────────────────────────────────────

    /**
     * Create a reverse/return pickup order in Shiprocket.
     * Used when customer requests a return and admin approves it.
     */
    public function createReturnPickup(array $returnOrderData): array
    {
        Log::info('Shiprocket: creating return pickup', ['order_id' => $returnOrderData['order_id'] ?? null]);
        return $this->call('post', '/orders/create/return', $returnOrderData);
    }

    // ──────────────────────────────────────────────────────────────
    // Utility: Build Order Items for Shiprocket
    // ──────────────────────────────────────────────────────────────

    /**
     * Convert our order items array into Shiprocket-compatible format.
     * Also calculates the total package weight using product variant data.
     *
     * @param  array $items  Order items from our DB
     * @return array ['items' => [...], 'total_weight' => float]
     */
    public function prepareOrderItems(array $items): array
    {
        $shiprocketItems = [];
        $totalWeight     = 0;

        foreach ($items as $item) {
            $itemWeight = 0.5; // default fallback per item

            // Try to get real weight from ProductVariant
            if (!empty($item['product_id']) && !empty($item['size'])) {
                try {
                    $variant = \App\Models\ProductVariant::where('product_id', $item['product_id'])
                        ->where('size', $item['size'])
                        ->first();

                    if ($variant && !empty($variant->weight)) {
                        $itemWeight = (float) $variant->weight;
                    }
                } catch (\Exception $e) {
                    Log::warning('Shiprocket: could not fetch variant weight', [
                        'product_id' => $item['product_id'],
                        'error'      => $e->getMessage(),
                    ]);
                }
            }

            $qty = (int) ($item['quantity'] ?? 1);

            $shiprocketItems[] = [
                'name'          => $item['name'],
                'sku'           => 'SKU-' . ($item['product_id'] ?? $item['id'] ?? uniqid()),
                'units'         => $qty,
                'selling_price' => (float) $item['price'],
                'discount'      => 0,
                'tax'           => 0,
                'hsn'           => '',
            ];

            $totalWeight += $qty * $itemWeight;
        }

        return [
            'items'        => $shiprocketItems,
            'total_weight' => max($totalWeight, 0.1), // Shiprocket minimum is 0.1 kg
        ];
    }
}
