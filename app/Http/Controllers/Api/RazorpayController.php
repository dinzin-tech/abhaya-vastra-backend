<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\WalletTransaction;
use App\Models\ProductVariant;
use App\Models\ProductColor;
use App\Models\User;
use App\Models\OrderReturn;
use App\Services\RewardService;
use App\Services\ShiprocketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\OrderPlacedMail;
use App\Mail\RefundReceivedMail;
use Razorpay\Api\Api;

class RazorpayController extends Controller
{
    private $razorpayApi;

    public function __construct()
    {
        // Get Razorpay credentials from database
        $gateway = PaymentGateway::where('gateway_name', 'razorpay')->first();
        
        if ($gateway) {
            $this->razorpayApi = new Api($gateway->api_key, $gateway->api_secret);
        }
    }

    /**
     * Create Razorpay order
     */
    public function createOrder(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'amount' => 'required|numeric',
            ]);

            $order = Order::find($request->order_id);
            $user = Auth::guard('sanctum')->user();

            if (!$user || $order->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Get gateway settings
            $gateway = PaymentGateway::where('gateway_name', 'razorpay')->first();
            
            if (!$gateway) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway not configured'
                ], 500);
            }

            // Create Razorpay order
            $razorpayOrder = $this->razorpayApi->order->create([
                'amount' => $request->amount * 100, // Amount in paise
                'currency' => $gateway->currency,
                'receipt' => $order->order_number,
                'notes' => [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                ]
            ]);

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'payment_method' => 'razorpay',
                'amount' => $request->amount,
                'currency' => $gateway->currency,
                'status' => 'pending',
                'razorpay_order_id' => $razorpayOrder['id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Razorpay order created successfully',
                'data' => [
                    'razorpay_order_id' => $razorpayOrder['id'],
                    'razorpay_key' => $gateway->api_key,
                    'amount' => $request->amount,
                    'currency' => $gateway->currency,
                    'name' => $user->name,
                    'email' => $user->email,
                    'contact' => $user->phone,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating Razorpay order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify Razorpay payment
     */
    public function verifyPayment(Request $request)
    {
        try {
            $request->validate([
                'razorpay_order_id' => 'required|string',
                'razorpay_payment_id' => 'required|string',
                'razorpay_signature' => 'required|string',
            ]);

            // Find payment record
            $payment = Payment::where('razorpay_order_id', $request->razorpay_order_id)->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            // Verify signature
            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature
            ];

            try {
                $this->razorpayApi->utility->verifyPaymentSignature($attributes);
                
                // Update payment record
                $payment->update([
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_signature' => $request->razorpay_signature,
                    'status' => 'completed',
                    'paid_at' => now(),
                ]);

                // Update order payment status
                $order = Order::find($payment->order_id);
                $order->update([
                    'payment_status' => 'completed'
                ]);

                // Reduce stock for ordered items
                $this->reduceStock($order);

                // Approve pending reward points
                $this->approvePendingRewards($order);

                Mail::to($order->email)->queue(new OrderPlacedMail($order));

                $adminEmail = config('mail.from.address') ?? 'admin@example.com';
                if (class_exists('App\Mail\AdminOrderNotificationMail')) {
                    Mail::to($adminEmail)->queue(new \App\Mail\AdminOrderNotificationMail($order));
                }

                // Automatically create Shiprocket shipment after payment success
                $this->createShiprocketShipment($order);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment verified successfully',
                    'data' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'payment_status' => 'completed'
                    ]
                ]);

            } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
                // Signature verification failed
                $payment->update([
                    'status' => 'failed',
                    'payment_details' => [
                        'error' => 'Signature verification failed',
                        'message' => $e->getMessage()
                    ]
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error verifying payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle payment failure
     */
    public function paymentFailed(Request $request)
    {
        try {
            $request->validate([
                'razorpay_order_id' => 'required|string',
                'error' => 'nullable|array',
            ]);

            $payment = Payment::where('razorpay_order_id', $request->razorpay_order_id)->first();

            if ($payment) {
                $payment->update([
                    'status' => 'failed',
                    'payment_details' => $request->error ?? ['message' => 'Payment failed']
                ]);

                // Update order payment status
                $order = Order::find($payment->order_id);
                $order->update([
                    'payment_status' => 'failed'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment failure recorded'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error recording payment failure: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process refund for approved return
     */
    public function processRefund(Request $request)
    {
        Log::info('🔄 Refund process started', $request->all());
        
        try {
            $request->validate([
                'payment_id' => 'required|string',
                'amount' => 'required|numeric',
                'return_id' => 'required|exists:order_returns,id',
            ]);

            Log::info('✅ Validation passed');

            // Get payment record
            $payment = Payment::where('razorpay_payment_id', $request->payment_id)->first();

            if (!$payment) {
                Log::error('❌ Payment not found', ['payment_id' => $request->payment_id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            Log::info('✅ Payment found', ['payment_id' => $payment->id, 'status' => $payment->status]);

            // Check if payment is completed
            if ($payment->status !== 'completed') {
                Log::error('❌ Payment not completed', ['status' => $payment->status]);
                return response()->json([
                    'success' => false,
                    'message' => 'Only completed payments can be refunded'
                ], 400);
            }

            // Get order details
            $order = Order::find($payment->order_id);
            Log::info('✅ Order found', ['order_id' => $order->id]);

            // Calculate refund amount
            $refundAmount = $request->amount;
            Log::info('💰 Refund amount calculated', ['amount' => $refundAmount]);

            // Process refund via Razorpay
            Log::info('🔄 Creating Razorpay refund...');
            $refund = $this->razorpayApi->refund->create([
                'payment_id' => $request->payment_id,
                'amount' => $refundAmount * 100, // Amount in paise
                'notes' => [
                    'return_id' => $request->return_id,
                    'refund_reason' => 'Return approved - Product refund (excluding shipping)'
                ]
            ]);

            Log::info('✅ Razorpay refund created', ['refund_id' => $refund['id']]);

            // Update payment record with refund details
            $payment->update([
                'refunded_amount' => $refundAmount,
                'refund_id' => $refund['id'],
                'refunded_at' => now(),
                'refund_details' => $refund
            ]);

            Log::info('✅ Payment record updated with refund details');

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'data' => [
                    'refund_id' => $refund['id'],
                    'refund_amount' => $refundAmount,
                    'status' => $refund['status']
                ]
            ]);

        } catch (\Razorpay\Api\Errors\Error $e) {
            Log::error('❌ Razorpay API Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Razorpay Error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('❌ General Error in processRefund', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error processing refund: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle successful refund webhook from Razorpay
     */
    public function handleRefundSuccess(Request $request)
    {
        try {
            // Verify webhook signature (implement proper verification)
            $webhookData = $request->all();

            // Extract refund ID from webhook data
            $refundId = $webhookData['payload']['refund']['entity']['id'] ?? null;

            if (!$refundId) {
                return response()->json(['success' => false, 'message' => 'Invalid refund data'], 400);
            }

            // Find payment record by refund ID
            $payment = Payment::where('refund_id', $refundId)->first();

            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Payment not found for refund'], 404);
            }

            // Update payment status to fully refunded
            $payment->update([
                'status' => 'refunded', 
                'refund_status' => 'completed'
            ]);

            // Find related return request
            $return = OrderReturn::where('refund_id', $refundId)->first();

            if ($return) {
                // Update return status to completed
                $return->update([
                    'status' => 'completed',
                    'refund_received_at' => now()
                ]);

                // Send confirmation email to user
                try {
                    $user = User::find($return->user_id);
                    if ($user) {
                        // Send refund received confirmation email
                        Mail::to($user->email)->queue(new RefundReceivedMail($return, $payment));
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send refund received email: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Refund success processed'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error processing refund success webhook: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing webhook'
            ], 500);
        }
    }

    /**
     * Reduce stock for ordered items
     */
    // private function reduceStock($order)
    // {
    //     try {
    //         foreach ($order->items as $item) {
    //             if (isset($item['product_id']) && isset($item['size']) && isset($item['color'])) {
    //                 // Find the color ID from color name
    //                 $color = ProductColor::where('color', $item['color'])->first();
                    
    //                 if ($color) {
    //                     // Find the variant
    //                     $variant = ProductVariant::where('product_id', $item['product_id'])
    //                         ->where('color_id', $color->id)
    //                         ->where('size', $item['size'])
    //                         ->first();
                        
    //                     if ($variant) {
    //                         // Reduce stock
    //                         $newStock = max(0, $variant->stock - $item['quantity']);
    //                         $variant->update(['stock' => $newStock]);
                            
    //                         Log::info('Stock reduced', [
    //                             'product_id' => $item['product_id'],
    //                             'variant_id' => $variant->id,
    //                             'color' => $item['color'],
    //                             'size' => $item['size'],
    //                             'quantity_reduced' => $item['quantity'],
    //                             'old_stock' => $variant->stock + $item['quantity'],
    //                             'new_stock' => $newStock
    //                         ]);
    //                     }
    //                 }
    //             }
    //         }
    //     } catch (\Exception $e) {
    //         // Log error but don't fail the payment
    //         Log::error('Failed to reduce stock: ' . $e->getMessage(), [
    //             'order_id' => $order->id
    //         ]);
    //     }
    // }
    private function reduceStock($order)
{
    try {
        Log::info('Starting stock reduction for order', ['order_id' => $order->id]);

        foreach ($order->items as $item) {
            Log::info('Processing order item', [
                'order_id' => $order->id,
                'item' => $item
            ]);

            if (!isset($item['product_id']) || !isset($item['size'])) {
                Log::warning('Missing product_id or size — skipping item', [
                    'order_id' => $order->id,
                    'item' => $item
                ]);
                continue;
            }

            $variantQuery = ProductVariant::where('product_id', $item['product_id'])
                ->where('size', $item['size']);

            // Optional color check
            if (!empty($item['color'])) {
                $color = ProductColor::where('color', $item['color'])->first();

                if ($color) {
                    $variantQuery->where('color_id', $color->id);
                    Log::info('Color found for variant', [
                        'color' => $item['color'],
                        'color_id' => $color->id,
                        'product_id' => $item['product_id']
                    ]);
                } else {
                    Log::warning('Color not found in ProductColor table', [
                        'product_id' => $item['product_id'],
                        'color' => $item['color']
                    ]);
                }
            } else {
                Log::info('No color provided, fetching variant only by size', [
                    'product_id' => $item['product_id'],
                    'size' => $item['size']
                ]);
            }

            $variant = $variantQuery->first();

            if ($variant) {
                Log::info('Variant found before stock update', [
                    'variant_id' => $variant->id,
                    'product_id' => $item['product_id'],
                    'current_stock' => $variant->stock
                ]);

                $oldStock = $variant->stock;
                $newStock = max(0, $oldStock - $item['quantity']);
                $variant->update(['stock' => $newStock]);

                Log::info('Stock reduced successfully', [
                    'product_id' => $item['product_id'],
                    'variant_id' => $variant->id,
                    'color' => $item['color'] ?? 'N/A',
                    'size' => $item['size'],
                    'quantity_reduced' => $item['quantity'],
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock
                ]);
            } else {
                Log::warning('No matching variant found — skipping reduction', [
                    'product_id' => $item['product_id'],
                    'size' => $item['size'],
                    'color' => $item['color'] ?? 'N/A',
                    'order_id' => $order->id
                ]);
            }
        }

        Log::info('Stock reduction completed for order', ['order_id' => $order->id]);

    } catch (\Exception $e) {
        Log::error('Failed to reduce stock', [
            'order_id' => $order->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}


    /**
     * Create Shiprocket shipment automatically after payment success
     */
    // private function createShiprocketShipment($order)
    // {
    //     try {
    //         // Check if shipment already created
    //         if ($order->shiprocket_order_id) {
    //             return;
    //         }

    //         $shiprocketService = new ShiprocketService();

    //         // Prepare order items for Shiprocket
    //         $orderItems = [];
    //         $totalWeight = 0;

    //         foreach ($order->items as $item) {
    //             // Get actual weight from product variant
    //             $itemWeight = 0.5; // Default weight if variant not found
                
    //             if (isset($item['product_id']) && isset($item['size']) && isset($item['color'])) {
    //                 // Find the color ID from color name
    //                 $color = ProductColor::where('color', $item['color'])->first();
                    
    //                 if ($color) {
    //                     // Find the variant
    //                     $variant = ProductVariant::where('product_id', $item['product_id'])
    //                         ->where('color_id', $color->id)
    //                         ->where('size', $item['size'])
    //                         ->first();
                        
    //                     // Use variant weight if found and not null
    //                     if ($variant && $variant->weight) {
    //                         $itemWeight = (float) $variant->weight;
    //                     }
    //                 }
    //             }
                
    //             $orderItems[] = [
    //                 'name' => $item['name'],
    //                 'sku' => $item['id'] ?? 'SKU-' . $item['id'],
    //                 'units' => $item['quantity'],
    //                 'selling_price' => $item['price'],
    //                 'discount' => 0,
    //                 'tax' => 0,
    //                 'hsn' => '',
    //             ];

    //             // Calculate total weight using actual variant weight
    //             $totalWeight += ($item['quantity'] * $itemWeight);
    //         }

    //         // Prepare Shiprocket order data
    //         $shiprocketOrderData = [
    //             'order_id' => $order->order_number,
    //             'order_date' => $order->created_at->format('Y-m-d H:i'),
    //             'pickup_location' => config('services.shiprocket.pickup_location', 'Primary'),
    //             'channel_id' => '',
    //             'comment' => 'Order from ' . config('app.name'),
    //             'billing_customer_name' => $order->name,
    //             'billing_last_name' => '',
    //             'billing_address' => $order->address,
    //             'billing_address_2' => '',
    //             'billing_city' => $order->city,
    //             'billing_pincode' => $order->zip,
    //             'billing_state' => $order->state ?? '',
    //             'billing_country' => 'India',
    //             'billing_email' => $order->email,
    //             'billing_phone' => $order->phone,
    //             'shipping_is_billing' => true,
    //             'shipping_customer_name' => '',
    //             'shipping_last_name' => '',
    //             'shipping_address' => '',
    //             'shipping_address_2' => '',
    //             'shipping_city' => '',
    //             'shipping_pincode' => '',
    //             'shipping_country' => '',
    //             'shipping_state' => '',
    //             'shipping_email' => '',
    //             'shipping_phone' => '',
    //             'order_items' => $orderItems,
    //             'payment_method' => 'Prepaid',
    //             'shipping_charges' => $order->shipping_charge ?? 0,
    //             'giftwrap_charges' => 0,
    //             'transaction_charges' => 0,
    //             'total_discount' => $order->discount ?? 0,
    //             'sub_total' => $order->subtotal,
    //             'length' => 10,
    //             'breadth' => 10,
    //             'height' => 10,
    //             'weight' => $totalWeight,
    //         ];

    //         // Log weight being sent to Shiprocket
    //         Log::info('📦 Shiprocket Weight Submission', [
    //             'order_number' => $order->order_number,
    //             'total_weight_kg' => $totalWeight,
    //             'items_count' => count($order->items)
    //         ]);
            
    //         // Write to debug file for easy viewing
    //         $debugInfo = "\n" . str_repeat('=', 50) . "\n";
    //         $debugInfo .= date('Y-m-d H:i:s') . "\n";
    //         $debugInfo .= "Order: {$order->order_number}\n";
    //         $debugInfo .= "Weight sent to Shiprocket: {$totalWeight} kg\n";
    //         $debugInfo .= "Items: " . count($order->items) . "\n";
    //         $debugInfo .= str_repeat('=', 50) . "\n";
    //         file_put_contents(storage_path('logs/shiprocket-weight.log'), $debugInfo, FILE_APPEND);

    //         // Create order in Shiprocket
    //         $result = $shiprocketService->createOrder($shiprocketOrderData);

    //         if ($result && isset($result['order_id'])) {
    //             // Update order with Shiprocket details
    //             $order->update([
    //                 'shiprocket_order_id' => $result['order_id'],
    //                 'shiprocket_shipment_id' => $result['shipment_id'],
    //                 'status' => 'processing',
    //             ]);

    //             Log::info('Shiprocket shipment created successfully', [
    //                 'order_id' => $order->id,
    //                 'shiprocket_order_id' => $result['order_id'],
    //                 'shiprocket_shipment_id' => $result['shipment_id']
    //             ]);
    //         }

    //     } catch (\Exception $e) {
    //         // Log error but don't fail the payment verification
    //         Log::error('Failed to create Shiprocket shipment: ' . $e->getMessage(), [
    //             'order_id' => $order->id
    //         ]);
    //     }
    // }

    private function createShiprocketShipment($order)
    {
        try {
            // Check if shipment already created
            if ($order->shiprocket_order_id) {
                return;
            }
    
            $shiprocketService = new ShiprocketService();
    
            // Prepare order items for Shiprocket
            $orderItems = [];
            $totalWeight = 0;
    
            foreach ($order->items as $item) {
                // Default weight if variant not found
                $itemWeight = 0.5;
    
                // Fetch variant weight based only on product_id and size
                if (!empty($item['product_id']) && !empty($item['size'])) {
                    $variant = ProductVariant::where('product_id', $item['product_id'])
                        ->where('size', $item['size'])
                        ->first();
    
                    if ($variant && !empty($variant->weight)) {
                        $itemWeight = (float) $variant->weight;
                    }
    
                    // Log variant check info for debugging
                    Log::info('Variant weight check (product_id + size)', [
                        'product_id' => $item['product_id'],
                        'size' => $item['size'],
                        'variant_found' => $variant ? true : false,
                        'variant_weight' => $variant->weight ?? 'N/A',
                        'used_weight' => $itemWeight,
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
                $totalWeight += ($item['quantity'] * $itemWeight);
            }
    
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
                'billing_state' => $order->state ?? '',
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
    
            // Log weight being sent to Shiprocket
            Log::info('📦 Shiprocket Weight Submission', [
                'order_number' => $order->order_number,
                'total_weight_kg' => $totalWeight,
                'items_count' => count($order->items)
            ]);
    
            // Write to debug file for easy viewing
            $debugInfo = "\n" . str_repeat('=', 50) . "\n";
            $debugInfo .= date('Y-m-d H:i:s') . "\n";
            $debugInfo .= "Order: {$order->order_number}\n";
            $debugInfo .= "Weight sent to Shiprocket: {$totalWeight} kg\n";
            $debugInfo .= "Items: " . count($order->items) . "\n";
            $debugInfo .= str_repeat('=', 50) . "\n";
            file_put_contents(storage_path('logs/shiprocket-weight.log'), $debugInfo, FILE_APPEND);
    
            // Create order in Shiprocket
            $result = $shiprocketService->createOrder($shiprocketOrderData);
    
            if ($result && isset($result['order_id'])) {
                // Update order with Shiprocket details
                $order->update([
                    'shiprocket_order_id' => $result['order_id'],
                    'shiprocket_shipment_id' => $result['shipment_id'],
                    'status' => 'processing',
                ]);
    
                Log::info('Shiprocket shipment created successfully', [
                    'order_id' => $order->id,
                    'shiprocket_order_id' => $result['order_id'],
                    'shiprocket_shipment_id' => $result['shipment_id']
                ]);
            }
    
        } catch (\Exception $e) {
            // Log error but don't fail the payment verification
            Log::error('Failed to create Shiprocket shipment: ' . $e->getMessage(), [
                'order_id' => $order->id
            ]);
        }
    }

    /**
     * Approve pending reward points for completed order
     */
    protected function approvePendingRewards(Order $order)
    {
        try {
            // Find pending reward transaction for this order
            $pendingTransaction = WalletTransaction::where('reference', 'order_' . $order->id)
                ->where('status', 'pending')
                ->where('type', 'credit')
                ->first();

            if ($pendingTransaction) {
                $rewardService = new RewardService($order->user_id);
                $rewardService->approveTransaction($pendingTransaction);
            }
        } catch (\Exception $e) {
            // Log error but don't fail the payment
            \Log::error('Failed to approve pending rewards: ' . $e->getMessage());
        }
    }
}
