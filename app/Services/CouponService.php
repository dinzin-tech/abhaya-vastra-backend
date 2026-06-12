<?php

namespace App\Services;

use App\Models\Coupon;
use Carbon\Carbon;

class CouponService
{
    protected $coupon;

    public function __construct($code){
        $this->coupon = Coupon::where('code', $code)->first();
    }

    public function validate(string $code, float $cartTotal): array
    {
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return ['valid' => false, 'message' => 'Coupon does not exist'];
        }

        if (!$coupon->is_active) {
            return ['valid' => false, 'message' => 'Coupon is inactive'];
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return ['valid' => false, 'message' => 'Coupon usage limit reached'];
        }

        $now = Carbon::now();
        if ($coupon->valid_from && $now->lt($coupon->valid_from)) {
            return ['valid' => false, 'message' => 'Coupon not yet valid'];
        }

        if ($coupon->valid_until && $now->gt($coupon->valid_until)) {
            return ['valid' => false, 'message' => 'Coupon expired'];
        }

        $discount = $coupon->type === 'percentage'
            ? ($cartTotal * $coupon->value / 100)
            : $coupon->value;

        return ['valid' => true, 'discount' => $discount, 'coupon' => $coupon];
    }

    public function incrementUsage()
    {
        $this->coupon->increment('used_count');
    }
}
