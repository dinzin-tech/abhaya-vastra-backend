<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\ProductColor;
use App\Services\ShiprocketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShiprocketController extends Controller
{
    private $shiprocketService;

    public function __construct(ShiprocketService $shiprocketService)
    {
        $this->shiprocketService = $shiprocketService;
    }

    /**
     * Test Shiprocket connection and authentication
     */
    public function testConnection(Request $request)
    {
        try {
            // Test authentication
            $token = $this->shiprocketService->getAuthToken();

            // Get pickup locations
            $pickupLocations = $this->shiprocketService->getPickupLocations();

            $response = [
                'success' => true,
                'message' => 'Shiprocket connection successful',
                'data' => [
                    'authenticated' => true,
                    'token_preview' => substr($token, 0, 30) . '...',
                    'email' => config('services.shiprocket.email'),
                    'pickup_location' => config('services.shiprocket.pickup_location'),
                    'pickup_pincode' => config('services.shiprocket.pickup_pincode'),
                    'available_pickup_locations' => $pickupLocations ? $pickupLocations['data']['shipping_address'] ?? [] : 'Unable to fetch'
                ]
            ];

            // If test order data provided, try creating a test order
            if ($request->has('test_order')) {
                $testOrderData = [
                    'order_id' => 'TEST-' . time(),
                    'order_date' => now()->format('Y-m-d H:i'),
                    'pickup_location' => config('services.shiprocket.pickup_location', 'Primary'),
                    'channel_id' => '',
                    'comment' => 'Test order from API',
                    'billing_customer_name' => 'Test Customer',
                    'billing_last_name' => '',
                    'billing_address' => 'Test Address Line 1',
                    'billing_address_2' => '',
                    'billing_city' => 'Kanchipuram',
                    'billing_pincode' => '603202',
                    'billing_state' => 'Tamil Nadu',
                    'billing_country' => 'India',
                    'billing_email' => 'test@example.com',
                    'billing_phone' => '9761813990',
                    'shipping_is_billing' => true,
                    'shipping_customer_name' => '',
                    'shipping_last_name' => '',
                    'shipping_address' => '',
                    'shipping_address_2' => '',
                    'shipping_city' => '',
                    'shipping_pincode' => '',
                    'shipping_country' => '',
                    'shipping_state' => '',
                    'shipping_email' => '',
                    'shipping_phone' => '',
                    'order_items' => [
                        [
                            'name' => 'Test Product',
                            'sku' => 'TEST-SKU-001',
                            'units' => 1,
                            'selling_price' => 100,
                            'discount' => 0,
                            'tax' => 0,
                            'hsn' => '',
                        ]
                    ],
                    'payment_method' => 'Prepaid',
                    'shipping_charges' => 0,
                    'giftwrap_charges' => 0,
                    'transaction_charges' => 0,
                    'total_discount' => 0,
                    'sub_total' => 100,
                    'length' => 10,
                    'breadth' => 10,
                    'height' => 10,
                    'weight' => 0.5,
                ];

                $orderResult = $this->shiprocketService->createOrder($testOrderData);
                $response['data']['test_order_created'] = true;
                $response['data']['shiprocket_response'] = $orderResult;
                $response['message'] = 'Test order created successfully in Shiprocket';
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Shiprocket connection failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check serviceability and get shipping charges
     */
    public function checkServiceability(Request $request)
    {
        try {
            $request->validate([
                'delivery_pincode' => 'required|string|digits:6',
                'weight' => 'nullable|numeric',
                'cod' => 'nullable|boolean',
            ]);

            $pickupPincode = config('services.shiprocket.pickup_pincode');
            $deliveryPincode = $request->delivery_pincode;
            $weight = $request->weight ?? 0.5; // Default 0.5 kg
            $cod = $request->cod ?? 0;

            $result = $this->shiprocketService->checkServiceability(
                $pickupPincode,
                $deliveryPincode,
                $weight,
                $cod
            );

            if ($result && isset($result['data']['available_courier_companies'])) {
                $couriers = $result['data']['available_courier_companies'];
                
                // Get the cheapest shipping rate
                $cheapestRate = null;
                $recommendedCourier = null;
                
                foreach ($couriers as $courier) {
                    if ($courier['freight_charge'] !== null) {
                        if ($cheapestRate === null || $courier['freight_charge'] < $cheapestRate) {
                            $cheapestRate = $courier['freight_charge'];
                            $recommendedCourier = $courier;
                        }
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Delivery available',
                    'data' => [
                        'is_serviceable' => true,
                        'shipping_charge' => $cheapestRate ?? 50, // Default ₹50 if not found
                        'estimated_delivery_days' => $recommendedCourier['etd'] ?? '5-7',
                        'courier_name' => $recommendedCourier['courier_name'] ?? 'Standard',
                        'all_couriers' => $couriers
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Delivery not available to this pincode',
                'data' => [
                    'is_serviceable' => false,
                    'shipping_charge' => 0
                ]
            ], 400);

        } catch (\Exception $e) {
            Log::error('Serviceability check error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error checking serviceability',
                'data' => [
                    'is_serviceable' => false,
                    'shipping_charge' => 50 // Default fallback
                ]
            ], 500);
        }
    }

    /**
     * Create Shiprocket order after payment success
     */
    public function createShipment(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
            ]);

            $order = Order::with('user')->find($request->order_id);

            // Check if order payment is completed
            if ($order->payment_status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order payment not completed'
                ], 400);
            }

            // Check if shipment already created
            if ($order->shiprocket_order_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shipment already created for this order'
                ], 400);
            }

            // Log order items structure for debugging
            Log::info('Order items structure:', ['items' => $order->items]);

            // Prepare order items for Shiprocket
            $orderItems = [];
            $totalWeight = 0;

            foreach ($order->items as $item) {
                // Get actual weight from product variant
                $itemWeight = 0.5; // Default weight if variant not found
                
                Log::info('Processing item:', [
                    'name' => $item['name'] ?? 'N/A',
                    'product_id' => $item['product_id'] ?? 'missing',
                    'size' => $item['size'] ?? 'missing',
                    'color' => $item['color'] ?? 'missing',
                    'quantity' => $item['quantity'] ?? 1
                ]);
                
                if (isset($item['product_id']) && isset($item['size']) && isset($item['color'])) {
                    // Find the color ID from color name
                    $color = ProductColor::where('color', $item['color'])->first();
                    
                    Log::info('Color lookup:', [
                        'color_name' => $item['color'],
                        'color_found' => $color ? 'Yes' : 'No',
                        'color_id' => $color ? $color->id : 'N/A'
                    ]);
                    
                    if ($color) {
                        // Find the variant
                        $variant = ProductVariant::where('product_id', $item['product_id'])
                            ->where('color_id', $color->id)
                            ->where('size', $item['size'])
                            ->first();
                        
                        Log::info('Variant lookup:', [
                            'product_id' => $item['product_id'],
                            'color_id' => $color->id,
                            'size' => $item['size'],
                            'variant_found' => $variant ? 'Yes' : 'No',
                            'variant_weight' => $variant ? $variant->weight : 'N/A'
                        ]);
                        
                        // Use variant weight if found and not null
                        if ($variant && $variant->weight) {
                            $itemWeight = (float) $variant->weight;
                            Log::info('Using variant weight:', ['weight' => $itemWeight]);
                        } else {
                            Log::warning('Variant weight not found, using default 0.5 kg');
                        }
                    }
                } else {
                    Log::warning('Missing variant info in order item:', [
                        'has_product_id' => isset($item['product_id']),
                        'has_size' => isset($item['size']),
                        'has_color' => isset($item['color'])
                    ]);
                }
                
                $orderItems[] = [
                    'name' => $item['name'],
                    'sku' => $item['id'] ?? 'SKU-' . $item['id'],
                    'units' => $item['quantity'],
                    'selling_price' => $item['price'],
                    'discount' => 0,
                    'tax' => 0,
                    'hsn' => '',
                ];

                // Calculate total weight using actual variant weight
                $itemTotalWeight = ($item['quantity'] * $itemWeight);
                $totalWeight += $itemTotalWeight;
                
                Log::info('Item weight calculation:', [
                    'item_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'unit_weight' => $itemWeight,
                    'total_item_weight' => $itemTotalWeight
                ]);
            }
            
            Log::info('Final weight calculation:', [
                'total_weight_kg' => $totalWeight,
                'total_items' => count($orderItems)
            ]);

            // Log weight BEFORE sending to Shiprocket
            Log::warning('⚠️ WEIGHT DEBUG: Total calculated weight', [
                'calculated_weight_kg' => $totalWeight,
                'weight_type' => gettype($totalWeight),
                'order_number' => $order->order_number
            ]);
            
            // Write weight debug to separate file for easy viewing
            $debugInfo = "\n========================================\n";
            $debugInfo .= date('Y-m-d H:i:s') . " - Order: {$order->order_number}\n";
            $debugInfo .= "CALCULATED WEIGHT: {$totalWeight} kg\n";
            $debugInfo .= "Items: " . count($order->items) . "\n";
            $debugInfo .= "========================================\n";
            file_put_contents(storage_path('logs/weight-debug.log'), $debugInfo, FILE_APPEND);

            // Prepare Shiprocket order data
            $shiprocketOrderData = [
                'order_id' => $order->order_number,
                'order_date' => $order->created_at->format('Y-m-d H:i'),
                'pickup_location' => config('services.shiprocket.pickup_location', 'Primary'),
                'channel_id' => '',
                'comment' => 'Order from ' . config('app.name'),
                'billing_customer_name' => $order->name,
                'billing_last_name' => '',
                'billing_address' => $order->address,
                'billing_address_2' => '',
                'billing_city' => $order->city,
                'billing_pincode' => $order->zip,
                'billing_state' => '',
                'billing_country' => 'India',
                'billing_email' => $order->email,
                'billing_phone' => $order->phone,
                'shipping_is_billing' => true,
                'shipping_customer_name' => '',
                'shipping_last_name' => '',
                'shipping_address' => '',
                'shipping_address_2' => '',
                'shipping_city' => '',
                'shipping_pincode' => '',
                'shipping_country' => '',
                'shipping_state' => '',
                'shipping_email' => '',
                'shipping_phone' => '',
                'order_items' => $orderItems,
                'payment_method' => 'Prepaid',
                'shipping_charges' => $order->shipping_charge ?? 0,
                'giftwrap_charges' => 0,
                'transaction_charges' => 0,
                'total_discount' => $order->discount ?? 0,
                'sub_total' => $order->subtotal,
                'length' => 10,
                'breadth' => 10,
                'height' => 10,
                'weight' => $totalWeight,
            ];

            // Final weight check before sending
            Log::warning('🚀 SENDING TO SHIPROCKET', [
                'order_number' => $order->order_number,
                'weight_in_payload' => $shiprocketOrderData['weight'],
                'full_order_data' => $shiprocketOrderData
            ]);
            
            Log::info('Shiprocket order data prepared:', ['order_data' => $shiprocketOrderData]);

            // Create order in Shiprocket
            $result = $this->shiprocketService->createOrder($shiprocketOrderData);

            if ($result && isset($result['order_id'])) {
                // Update order with Shiprocket details
                $order->update([
                    'shiprocket_order_id' => $result['order_id'],
                    'shiprocket_shipment_id' => $result['shipment_id'],
                    'status' => 'processing',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Shipment created successfully',
                    'data' => [
                        'shiprocket_order_id' => $result['order_id'],
                        'shiprocket_shipment_id' => $result['shipment_id'],
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create shipment in Shiprocket'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Shiprocket shipment creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating shipment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign AWB and generate pickup
     */
    public function assignAwbAndPickup(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'courier_id' => 'required|integer',
            ]);

            $order = Order::find($request->order_id);

            if (!$order->shiprocket_shipment_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shipment not created yet'
                ], 400);
            }

            // Assign AWB
            $awbResult = $this->shiprocketService->assignAwb(
                $order->shiprocket_shipment_id,
                $request->courier_id
            );

            if ($awbResult && isset($awbResult['awb_assign_status']) && $awbResult['awb_assign_status'] == 1) {
                $awbCode = $awbResult['response']['data']['awb_code'];
                $courierName = $awbResult['response']['data']['courier_name'];

                // Update order with AWB details
                $order->update([
                    'shiprocket_awb_code' => $awbCode,
                    'shiprocket_courier_name' => $courierName,
                ]);

                // Generate pickup
                $pickupResult = $this->shiprocketService->generatePickup($order->shiprocket_shipment_id);

                if ($pickupResult) {
                    return response()->json([
                        'success' => true,
                        'message' => 'AWB assigned and pickup generated successfully',
                        'data' => [
                            'awb_code' => $awbCode,
                            'courier_name' => $courierName,
                            'pickup_status' => $pickupResult
                        ]
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'AWB assigned successfully but pickup generation failed',
                    'data' => [
                        'awb_code' => $awbCode,
                        'courier_name' => $courierName,
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign AWB'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error assigning AWB: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Track shipment
     */
    public function trackShipment($orderId)
    {
        try {
            $order = Order::find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            if (!$order->shiprocket_awb_code) {
                return response()->json([
                    'success' => false,
                    'message' => 'AWB not assigned yet'
                ], 400);
            }

            $trackingData = $this->shiprocketService->trackShipment($order->shiprocket_awb_code);

            if ($trackingData) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tracking data fetched successfully',
                    'data' => $trackingData
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tracking data'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error tracking shipment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate shipping label
     */
    public function generateLabel(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
            ]);

            $order = Order::find($request->order_id);

            if (!$order->shiprocket_shipment_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shipment not created yet'
                ], 400);
            }

            $result = $this->shiprocketService->generateLabel([$order->shiprocket_shipment_id]);

            if ($result && isset($result['label_url'])) {
                return response()->json([
                    'success' => true,
                    'message' => 'Label generated successfully',
                    'data' => [
                        'label_url' => $result['label_url']
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate label'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating label: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate manifest
     */
    public function generateManifest(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
            ]);

            $order = Order::find($request->order_id);

            if (!$order->shiprocket_shipment_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shipment not created yet'
                ], 400);
            }

            $result = $this->shiprocketService->generateManifest([$order->shiprocket_shipment_id]);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Manifest generated successfully',
                    'data' => $result
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate manifest'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating manifest: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel shipment
     */
    public function cancelShipment(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
            ]);

            $order = Order::find($request->order_id);

            if (!$order->shiprocket_awb_code) {
                return response()->json([
                    'success' => false,
                    'message' => 'AWB not assigned yet'
                ], 400);
            }

            $result = $this->shiprocketService->cancelShipment([$order->shiprocket_awb_code]);

            if ($result) {
                // Update order status
                $order->update([
                    'status' => 'cancelled',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Shipment cancelled successfully',
                    'data' => $result
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel shipment'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling shipment: ' . $e->getMessage()
            ], 500);
        }
    }
}
