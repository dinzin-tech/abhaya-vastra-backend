<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\RewardSetting;
use App\Models\PointsConfig;
use App\Models\PaymentGateway;
use App\Services\RewardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Razorpay\Api\Api;

class WalletController extends Controller
{
    /**
     * Get user's wallet balance and loyalty points
     */
    public function getBalance(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'wallet_balance' => 0]
            );

            $settings = RewardSetting::where('status', 1)->first();
            $pointValue = $settings ? $settings->points_value : 1;

            return response()->json([
                'success' => true,
                'data' => [
                    'loyalty_points' => $wallet->balance,
                    'rupee_value' => $wallet->balance * $pointValue,
                    'point_value' => $pointValue,
                    'wallet_balance' => $wallet->wallet_balance,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching balance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get wallet transaction history
     */
    public function getTransactions(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0]
            );

            $transactions = WalletTransaction::where('wallet_id', $wallet->id)
                ->where('status', 'completed')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching transactions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Purchase loyalty points (convert money to points)
     */
    public function purchasePoints(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:1',
                'payment_method' => 'required|string',
                'payment_reference' => 'required|string',
            ]);

            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $settings = RewardSetting::where('status', 1)->first();
            
            if (!$settings) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reward system is not configured'
                ], 400);
            }

            // Calculate points from amount
            $points = RewardService::rupeesToPoints($request->amount);

            if ($points <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid amount'
                ], 400);
            }

            $rewardService = new RewardService($user->id);
            $transaction = $rewardService->addPoints(
                $points,
                'PURCHASE_' . $request->payment_reference,
                'Purchased ' . $points . ' loyalty points for ₹' . $request->amount
            );

            return response()->json([
                'success' => true,
                'message' => 'Loyalty points purchased successfully!',
                'data' => [
                    'points_added' => $points,
                    'amount_paid' => $request->amount,
                    'new_balance' => $rewardService->getBalance(),
                    'transaction' => $transaction
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error purchasing points: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply loyalty points at checkout (returns discount amount)
     */
    public function applyPoints(Request $request)
    {
        try {
            $request->validate([
                'points' => 'required|integer|min:1',
                'order_total' => 'required|numeric|min:0',
            ]);

            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $rewardService = new RewardService($user->id);
            $availablePoints = $rewardService->getBalance();

            if ($request->points > $availablePoints) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient loyalty points. Available: ' . $availablePoints
                ], 400);
            }

            // Calculate discount value
            $discountAmount = RewardService::pointsToRupees($request->points);

            // Ensure discount doesn't exceed order total
            if ($discountAmount > $request->order_total) {
                $discountAmount = $request->order_total;
                $pointsToUse = RewardService::rupeesToPoints($discountAmount);
            } else {
                $pointsToUse = $request->points;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'points_to_use' => $pointsToUse,
                    'discount_amount' => $discountAmount,
                    'new_total' => max(0, $request->order_total - $discountAmount),
                    'remaining_points' => $availablePoints - $pointsToUse
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error applying points: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Redeem loyalty points at checkout (actual deduction)
     */
    public function redeemPoints(Request $request)
    {
        try {
            $request->validate([
                'points' => 'required|integer|min:1',
                'order_id' => 'required|integer',
                'order_number' => 'required|string',
            ]);

            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $rewardService = new RewardService($user->id);
            $availablePoints = $rewardService->getBalance();

            if ($request->points > $availablePoints) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient loyalty points'
                ], 400);
            }

            $discountAmount = RewardService::pointsToRupees($request->points);

            $transaction = $rewardService->deductPoints(
                $request->points,
                'ORDER_' . $request->order_number,
                'Redeemed ' . $request->points . ' points for Order #' . $request->order_number
            );

            return response()->json([
                'success' => true,
                'message' => 'Loyalty points redeemed successfully!',
                'data' => [
                    'points_used' => $request->points,
                    'discount_amount' => $discountAmount,
                    'remaining_balance' => $rewardService->getBalance(),
                    'transaction' => $transaction
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error redeeming points: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reward settings for frontend
     */
    public function getSettings(Request $request)
    {
        try {
            $settings = RewardSetting::where('status', 1)->first();

            if (!$settings) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reward system is not active'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available wallet top-up packages
     */
    public function getTopUpPackages(Request $request)
    {
        try {
            $packages = PointsConfig::where('status', 1)
                ->orderBy('min_amount', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $packages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching packages: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create Razorpay order for wallet top-up
     */
    public function createTopUpOrder(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:1',
                'points' => 'required|integer|min:1',
            ]);

            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $gateway = PaymentGateway::where('gateway_name', 'razorpay')->first();
            
            if (!$gateway) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway not configured'
                ], 500);
            }

            $razorpayApi = new Api($gateway->api_key, $gateway->api_secret);

            $razorpayOrder = $razorpayApi->order->create([
                'amount' => $request->amount * 100,
                'currency' => $gateway->currency,
                'receipt' => 'WALLET_' . $user->id . '_' . time(),
                'notes' => [
                    'user_id' => $user->id,
                    'points' => $request->points,
                    'type' => 'wallet_topup'
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Razorpay order created',
                'data' => [
                    'razorpay_order_id' => $razorpayOrder['id'],
                    'razorpay_key' => $gateway->api_key,
                    'amount' => $request->amount,
                    'currency' => $gateway->currency,
                    'name' => $user->name,
                    'email' => $user->email,
                    'contact' => $user->phone,
                    'points' => $request->points,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify wallet top-up payment and credit points
     */
    public function verifyTopUpPayment(Request $request)
    {
        try {
            $request->validate([
                'razorpay_order_id' => 'required|string',
                'razorpay_payment_id' => 'required|string',
                'razorpay_signature' => 'required|string',
                'points' => 'required|integer|min:1',
                'amount' => 'required|numeric',
            ]);

            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $gateway = PaymentGateway::where('gateway_name', 'razorpay')->first();
            $razorpayApi = new Api($gateway->api_key, $gateway->api_secret);

            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature
            ];

            try {
                $razorpayApi->utility->verifyPaymentSignature($attributes);
                
                $rewardService = new RewardService($user->id);
                $transaction = $rewardService->addPoints(
                    $request->points,
                    'TOPUP_' . $request->razorpay_payment_id,
                    'Wallet top-up - ₹' . $request->amount . ' paid via Razorpay'
                );

                return response()->json([
                    'success' => true,
                    'message' => $request->points . ' points added to wallet!',
                    'data' => [
                        'points_added' => $request->points,
                        'amount_paid' => $request->amount,
                        'new_balance' => $rewardService->getBalance(),
                        'transaction' => $transaction
                    ]
                ]);
            } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create Razorpay order for wallet money top-up
     */
    public function createMoneyTopUpOrder(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:1',
            ]);

            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $gateway = PaymentGateway::where('gateway_name', 'razorpay')->first();
            
            if (!$gateway) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway not configured'
                ], 500);
            }

            $razorpayApi = new Api($gateway->api_key, $gateway->api_secret);

            $razorpayOrder = $razorpayApi->order->create([
                'amount' => $request->amount * 100,
                'currency' => $gateway->currency,
                'receipt' => 'WALLET_MONEY_' . $user->id . '_' . time(),
                'notes' => [
                    'user_id' => $user->id,
                    'amount' => $request->amount,
                    'type' => 'wallet_money_topup'
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Razorpay order created',
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
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify wallet money top-up payment and add money
     */
    public function verifyMoneyTopUpPayment(Request $request)
    {
        try {
            $request->validate([
                'razorpay_order_id' => 'required|string',
                'razorpay_payment_id' => 'required|string',
                'razorpay_signature' => 'required|string',
                'amount' => 'required|numeric',
            ]);

            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $gateway = PaymentGateway::where('gateway_name', 'razorpay')->first();
            $razorpayApi = new Api($gateway->api_key, $gateway->api_secret);

            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature
            ];

            try {
                $razorpayApi->utility->verifyPaymentSignature($attributes);
                
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => 0, 'wallet_balance' => 0]
                );

                // Add money to wallet
                $wallet->increment('wallet_balance', $request->amount);

                // Create transaction record
                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'credit',
                    'points' => 0,
                    'status' => 'completed',
                    'description' => 'Wallet money top-up - ₹' . $request->amount . ' added',
                    'reference' => 'MONEY_TOPUP_' . $request->razorpay_payment_id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => '₹' . $request->amount . ' added to wallet!',
                    'data' => [
                        'amount_added' => $request->amount,
                        'new_wallet_balance' => $wallet->wallet_balance,
                    ]
                ]);
            } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
