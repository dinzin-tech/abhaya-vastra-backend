<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    //

    public function check(Request $request)
    {
        // Log the incoming request
        \Log::info('Coupon Check Request:', $request->all());

        $validated = $request->validate([
            "code" => 'required|string',
            "amount" => 'required|numeric|min:0',
            "user_id" => 'nullable|exists:users,id'
        ]);

        $response = [
            'success' => false,
            'message' => 'Invalid Coupon Code',
            'data' => null
        ];

        // Find the coupon with detailed logging
        $coupon = Coupon::where('code', $request->code)
            ->where('status', true)
            ->first();

        if (!$coupon) {
            $message = 'Coupon not found or inactive.';
            \Log::warning($message, ['code' => $request->code]);
            $response['message'] = $message;
            return response()->json($response);
        }

        // Log coupon details
        \Log::info('Coupon Found:', [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'min_cart_amount' => $coupon->min_cart_amount,
            'usage_limit' => $coupon->usage_limit,
            'used_count' => $coupon->used_count,
            'expires_at' => $coupon->expires_at,
            'status' => $coupon->status,
        ]);

        // Check usage limit first
        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            $message = 'This coupon has reached its maximum usage limit.';
            \Log::warning($message, [
                'coupon_id' => $coupon->id,
                'used_count' => $coupon->used_count,
                'usage_limit' => $coupon->usage_limit
            ]);
            $response['message'] = $message;
            return response()->json($response);
        }

        // Check if user has already used this coupon
        if ($request->user_id) {
            $userCoupon = $coupon->users()
                ->where('user_id', $request->user_id)
                ->first();

            // Log user coupon status
            \Log::info('User Coupon Check:', [
                'user_id' => $request->user_id,
                'exists' => (bool)$userCoupon,
                'used' => $userCoupon ? (bool)$userCoupon->pivot->used : null,
                'used_at' => $userCoupon ? $userCoupon->pivot->used_at : null
            ]);

            if ($userCoupon && $userCoupon->pivot->used) {
                $message = 'You have already used this coupon.';
                \Log::warning($message, [
                    'user_id' => $request->user_id,
                    'coupon_id' => $coupon->id
                ]);
                $response['message'] = $message;
                return response()->json($response);
            }
        }

        // Check validity with detailed logging
        $isValid = $coupon->isValid($request->amount, $request->user_id);
        
        // Log the validation result
        \Log::info('Coupon Validation Result:', [
            'is_valid' => $isValid,
            'amount' => $request->amount,
            'min_amount' => $coupon->min_cart_amount,
            'is_expired' => $coupon->expires_at && \Carbon\Carbon::parse($coupon->expires_at)->isPast(),
            'usage_limit_reached' => $coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit,
            'amount_too_low' => $request->amount < $coupon->min_cart_amount
        ]);

        if (!$isValid) {
            if ($coupon->expires_at && \Carbon\Carbon::parse($coupon->expires_at)->isPast()) {
                $message = 'This coupon has expired.';
            } elseif ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
                $message = 'This coupon has reached its usage limit.';
            } elseif ($request->amount < $coupon->min_cart_amount) {
                $message = 'Cart amount must be at least ₹' . number_format($coupon->min_cart_amount, 2);
            } else {
                $message = 'This coupon is not valid for your order.';
            }

            \Log::warning('Coupon Validation Failed: ' . $message, [
                'coupon_id' => $coupon->id,
                'user_id' => $request->user_id,
                'amount' => $request->amount
            ]);

            $response['message'] = $message;
            return response()->json($response);
        }

        // Mark coupon as used for this user
        if ($request->user_id) {
            $coupon->users()->syncWithoutDetaching([
                $request->user_id => [
                    'used' => true,
                    'used_at' => now()
                ]
            ]);
            
            // Increment used count
            $coupon->increment('used_count');
        }

        // Calculate discount
        $discount = $coupon->getDiscount($request->amount);
        $finalAmount = $request->amount - $discount;

        $response = [
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'data' => [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'discount' => $discount,
                'final_amount' => max(0, $finalAmount),
                'expires_at' => $coupon->expires_at ? (is_string($coupon->expires_at) ? Carbon::parse($coupon->expires_at)->format('Y-m-d') : $coupon->expires_at->format('Y-m-d')) : null,
            ]
        ];

        return response()->json($response);
    }


    /**
     * Update coupon usage after successful order
     */
    public function updateUsage(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found.',
            ], 404);
        }

        // Check if user has already used this coupon
        $userCoupon = $coupon->users()
            ->where('user_id', $request->user_id)
            ->wherePivot('used', true)
            ->exists();

        if ($userCoupon) {
            return response()->json([
                'success' => false,
                'message' => 'You have already used this coupon.',
            ], 400);
        }

        // Mark coupon as used by this user
        $coupon->markAsUsed($request->user_id);

        return response()->json([
            'success' => true,
            'message' => 'Coupon used successfully.',
            'data' => [
                'code' => $coupon->code,
                'used_count' => $coupon->used_count,
            ]
        ]);
    }


}
