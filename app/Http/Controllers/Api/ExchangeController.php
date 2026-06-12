<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderExchange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Razorpay\Api\Api;
use App\Mail\ExchangeRequestedMail;
use App\Mail\AdminExchangeNotificationMail;
use App\Models\PaymentGateway;

class ExchangeController extends Controller
{
    /**
     * Get all exchange requests for the authenticated user
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $exchanges = OrderExchange::where('user_id', $user->id)
                ->with(['order'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Exchange requests fetched successfully',
                'data' => $exchanges
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching exchange requests: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new exchange request
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'order_id' => 'required|exists:orders,id',
                'product_id' => 'required|integer',
                'original_size' => 'required|string',
                'original_color' => 'nullable|string',
                'exchange_size' => 'required|string',
                'exchange_color' => 'nullable|string',
                'reason' => 'required|string',
                'images' => 'nullable|array',
                'images.*' => 'string', // Base64 encoded images
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get the order
            $order = Order::where('id', $request->order_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Check if order can be exchanged
            if (!$order->canBeExchanged()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This order cannot be exchanged. Exchanges are only allowed within 7 days of delivery.'
                ], 422);
            }

            // Check if order already has an exchange request
            if ($order->hasExchangeRequest()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An exchange request already exists for this order.'
                ], 422);
            }

            // Validate that user is exchanging same product with different size/color
            if ($request->original_size === $request->exchange_size && 
                $request->original_color === $request->exchange_color) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exchange size or color must be different from the original.'
                ], 422);
            }

            // Process and save images
            $savedImageUrls = [];
            if ($request->has('images') && is_array($request->images)) {
                foreach ($request->images as $base64Image) {
                    if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
                        $imageType = $matches[1];
                        $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
                        $imageData = base64_decode($imageData);

                        if ($imageData !== false) {
                            $filename = 'exchange_' . $order->id . '_' . time() . '_' . uniqid() . '.' . $imageType;
                            $path = 'exchanges/' . $filename;

                            Storage::disk('public')->put($path, $imageData);
                            $savedImageUrls[] = asset('storage/' . $path);
                        }
                    }
                }
            }

            // Create exchange request
            $exchange = OrderExchange::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'product_id' => $request->product_id,
                'original_size' => $request->original_size,
                'original_color' => $request->original_color,
                'exchange_size' => $request->exchange_size,
                'exchange_color' => $request->exchange_color,
                'reason' => $request->reason,
                'images' => $savedImageUrls,
                'exchange_charge' => 100.00,
                'status' => 'pending',
                'payment_status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Exchange request created successfully. Please proceed with payment.',
                'data' => $exchange->load(['order'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating exchange request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create Razorpay order for exchange charge payment
     */
    public function createPaymentOrder(Request $request, $exchangeId)
    {
        try {
            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $exchange = OrderExchange::where('id', $exchangeId)
                ->where('user_id', $user->id)
                ->first();

            if (!$exchange) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exchange request not found'
                ], 404);
            }

            if ($exchange->payment_status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment already completed for this exchange'
                ], 422);
            }

            // Get Razorpay credentials from database
            $gateway = PaymentGateway::where('gateway_name', 'razorpay')->first();
            
            if (!$gateway) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway not configured'
                ], 500);
            }

            // Initialize Razorpay
            $api = new Api($gateway->api_key, $gateway->api_secret);

            // Create Razorpay order
            $orderData = [
                'receipt' => 'exchange_' . $exchange->id . '_' . time(),
                'amount' => $exchange->exchange_charge * 100, // Convert to paise
                'currency' => 'INR',
                'notes' => [
                    'exchange_id' => $exchange->id,
                    'order_id' => $exchange->order_id,
                    'user_id' => $user->id
                ]
            ];

            $razorpayOrder = $api->order->create($orderData);

            // Update exchange with Razorpay order ID
            $exchange->update([
                'razorpay_order_id' => $razorpayOrder->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment order created successfully',
                'data' => [
                    'order_id' => $razorpayOrder->id,
                    'amount' => $exchange->exchange_charge,
                    'currency' => $gateway->currency,
                    'key' => $gateway->api_key,
                    'exchange' => $exchange
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating payment order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify Razorpay payment for exchange
     */
    public function verifyPayment(Request $request, $exchangeId)
    {
        try {
            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'razorpay_payment_id' => 'required|string',
                'razorpay_order_id' => 'required|string',
                'razorpay_signature' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $exchange = OrderExchange::where('id', $exchangeId)
                ->where('user_id', $user->id)
                ->first();

            if (!$exchange) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exchange request not found'
                ], 404);
            }

            // Get Razorpay credentials from database
            $gateway = PaymentGateway::where('gateway_name', 'razorpay')->first();
            
            if (!$gateway) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway not configured'
                ], 500);
            }

            // Verify signature
            $api = new Api($gateway->api_key, $gateway->api_secret);
            
            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature
            ];

            try {
                $api->utility->verifyPaymentSignature($attributes);
            } catch (\Exception $e) {
                $exchange->update([
                    'payment_status' => 'failed'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed'
                ], 422);
            }

            // Update exchange with payment details
            $exchange->update([
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'payment_status' => 'paid',
                'status' => 'pending' // Keep status as pending until admin approves
            ]);

            // Send email notifications
            try {
                Mail::to($exchange->order->email)->queue(new ExchangeRequestedMail($exchange->load('order')));
                
                $adminEmail = config('mail.from.address') ?? 'admin@example.com';
                Mail::to($adminEmail)->queue(new AdminExchangeNotificationMail($exchange->load('order')));
            } catch (\Exception $e) {
                \Log::error('Failed to send exchange notification emails: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully. Your exchange request has been submitted.',
                'data' => $exchange->load(['order'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error verifying payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific exchange request details
     */
    public function show($id)
    {
        try {
            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $exchange = OrderExchange::where('id', $id)
                ->where('user_id', $user->id)
                ->with(['order'])
                ->first();

            if (!$exchange) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exchange request not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Exchange request details fetched successfully',
                'data' => $exchange
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching exchange request: ' . $e->getMessage()
            ], 500);
        }
    }
}
