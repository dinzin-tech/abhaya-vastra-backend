<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Cart;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\CouponService;
use App\Services\RewardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Get all orders for the authenticated user
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

            $orders = Order::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Orders fetched successfully',
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Place a new order
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'address' => 'required|string',
                'city' => 'required|string|max:255',
                'zip' => 'required|string|max:20',
                'items' => 'required|array',
                'subtotal' => 'required|numeric',
                'total' => 'required|numeric',
                'payment_method' => 'required|in:razorpay',
                'password' => 'nullable|string|min:6',
            ]);

            $user = Auth::guard('sanctum')->user();

            // If user is not logged in, check if email exists or create new user
            if (!$user) {
                $existingUser = User::where('email', $request->email)->first();

                if ($existingUser) {
                    // Email exists, user should login
                    return response()->json([
                        'success' => false,
                        'message' => 'Email already exists. Please login to continue.'
                    ], 422);
                }

                // Create new user for guest checkout
                if (!$request->password) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Password is required for new users'
                    ], 422);
                }

                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'city' => $request->city,
                    'city' => $request->state,
                    'zip' => $request->zip,
                    'password' => Hash::make($request->password),
                ]);

                // Auto-login the newly created user
                $token = $user->createToken('auth_token')->plainTextToken;
            } else {
                // Update existing user's address if provided
                $user->update([
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'zip' => $request->zip,
                ]);
            }

            // Generate unique order number
            $orderNumber = 'ORD-' . strtoupper(Str::random(10));

            // Create order - payment status will be pending until Razorpay confirms
            $paymentStatus = 'pending';

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $orderNumber,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state ?? '',
                'zip' => $request->zip,
                'items' => $request->items,
                'subtotal' => $request->subtotal,
                'discount' => $request->discount ?? 0,
                'shipping_charge' => $request->shipping_charge ?? 0,
                'total' => $request->total,
                'coupon_code' => $request->coupon_code ?? null,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_status' => $paymentStatus
            ]);

            // Handle wallet money deduction
            if ($request->use_wallet_money && $request->wallet_money_used > 0) {
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => 0, 'wallet_balance' => 0]
                );

                if ($wallet->wallet_balance >= $request->wallet_money_used) {
                    $wallet->decrement('wallet_balance', $request->wallet_money_used);
                    
                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'type' => 'debit',
                        'points' => 0,
                        'status' => 'completed',
                        'description' => 'Used ₹' . $request->wallet_money_used . ' for Order #' . $order->order_number,
                        'reference' => 'ORDER_' . $order->id,
                    ]);
                    
                    // Update order with wallet usage
                    $order->update(['wallet_money_used' => $request->wallet_money_used]);
                }
            }

            // Handle loyalty points deduction
            if ($request->use_loyalty_points && $request->loyalty_points_used > 0) {
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => 0, 'wallet_balance' => 0]
                );

                if ($wallet->balance >= $request->loyalty_points_used) {
                    $wallet->decrement('balance', $request->loyalty_points_used);
                    
                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'type' => 'debit',
                        'points' => $request->loyalty_points_used,
                        'status' => 'completed',
                        'description' => 'Used ' . $request->loyalty_points_used . ' loyalty points for Order #' . $order->order_number,
                        'reference' => 'ORDER_' . $order->id,
                    ]);
                    
                    // Update order with points usage
                    $order->update(['loyalty_points_used' => $request->loyalty_points_used]);
                }
            }



            // Update Coupon usage 
            if ($order && !empty($request->coupon_code)) {
                $couponService = new CouponService($request->coupon_code);
                $couponService->incrementUsage();
            }

            // process rewards
            if ($order) {
                $rewardService = new RewardService($user->id);
                $reward = $rewardService->createPendingCredit((float)$order->total, 'order_' . $order->id, 'Reward points for Order #' . $order->order_number);
            }

            $response = [
                'success' => true,
                'message' => 'Order placed successfully',
                'data' => [
                    'order' => $order,
                    'reward' => $reward ?? null
                ]
            ];

            // If new user was created, include token
            if (isset($token)) {
                $response['data']['token'] = $token;
                $response['data']['user'] = $user;
                $response['message'] = 'Order placed successfully. Account created and logged in.';
            }

            return response()->json($response, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error placing order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific order details
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

            $order = Order::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order details fetched successfully',
                'data' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete order (only if payment is cancelled/failed)
     */
    public function destroy($id)
    {
        try {
            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $order = Order::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Only allow deletion if payment is pending or failed
            if ($order->payment_status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete order with completed payment'
                ], 403);
            }

            // Revert used wallet money & loyalty points back to user's wallet
            if ($order->wallet_money_used > 0 || $order->loyalty_points_used > 0) {
                $wallet = \App\Models\Wallet::where('user_id', $order->user_id)->first();
                if ($wallet) {
                    if ($order->wallet_money_used > 0) {
                        $wallet->increment('wallet_balance', $order->wallet_money_used);
                        \App\Models\WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'type' => 'credit',
                            'points' => 0,
                            'status' => 'completed',
                            'description' => 'Reverted ₹' . $order->wallet_money_used . ' due to cancelled order #' . $order->order_number,
                            'reference' => 'REVERT_' . $order->id,
                        ]);
                    }
                    if ($order->loyalty_points_used > 0) {
                        $wallet->increment('balance', $order->loyalty_points_used);
                        \App\Models\WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'type' => 'credit',
                            'points' => $order->loyalty_points_used,
                            'status' => 'completed',
                            'description' => 'Reverted ' . $order->loyalty_points_used . ' points due to cancelled order #' . $order->order_number,
                            'reference' => 'REVERT_' . $order->id,
                        ]);
                    }
                }
            }

            // Revert coupon usage
            if (!empty($order->coupon_code)) {
                $coupon = \App\Models\Coupon::where('code', $order->coupon_code)->first();
                if ($coupon) {
                    $coupon->decrement('used_count');
                }
            }

            // Delete associated payment records
            Payment::where('order_id', $order->id)->delete();

            // Delete the order
            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting order: ' . $e->getMessage()
            ], 500);
        }
    }
}
