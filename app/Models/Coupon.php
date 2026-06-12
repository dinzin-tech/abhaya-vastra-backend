<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_cart_amount',
        'usage_limit',
        'used_count',
        'expires_at',
        'status'
    ];

    protected $dates = [
        'expires_at'
    ];

    /**
     * The users that belong to the coupon.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'coupon_user')
            ->withPivot('used')
            ->withTimestamps();
    }

    /**
     * Mark coupon as used by a specific user
     */
    public function markAsUsed($userId)
    {
        $this->increment('used_count');
        
        // Update or create pivot record
        $this->users()->syncWithoutDetaching([
            $userId => ['used' => true, 'used_at' => now()]
        ]);
        
        return $this;
    }



    public function isValid($cartAmount, $userId = null)
    {
        // Check if coupon is active
        if (!$this->status) {
            \Log::info('Coupon is inactive', ['coupon_id' => $this->id]);
            return false;
        }

        // Check if coupon has expired
        if ($this->expires_at && Carbon::parse($this->expires_at)->isPast()) {
            \Log::info('Coupon has expired', ['coupon_id' => $this->id, 'expires_at' => $this->expires_at]);
            return false;
        }

        // Check usage limit
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            \Log::info('Coupon usage limit reached', [
                'coupon_id' => $this->id, 
                'used_count' => $this->used_count, 
                'usage_limit' => $this->usage_limit
            ]);
            return false;
        }

        // Check minimum cart amount
        if ($cartAmount < $this->min_cart_amount) {
            \Log::info('Cart amount too low', [
                'coupon_id' => $this->id, 
                'cart_amount' => $cartAmount, 
                'min_cart_amount' => $this->min_cart_amount
            ]);
            return false;
        }

        // Check if user has already used this coupon
        if ($userId) {
            $userCoupon = $this->users()
                ->where('user_id', $userId)
                ->wherePivot('used', true)
                ->first();

            if ($userCoupon) {
                \Log::info('User has already used this coupon', [
                    'coupon_id' => $this->id, 
                    'user_id' => $userId
                ]);
                return false;
            }
        }

        return true;
    }


    public function getDiscount($cartAmount)
    {
        if ($this->type === 'fixed') {
            return min($this->value, $cartAmount);
        } elseif ($this->type === 'percentage') {
            return round(($this->value / 100) * $cartAmount, 2);
        }
        return 0;
    }
}
