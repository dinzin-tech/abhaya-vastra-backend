<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Coupon;

class CouponUser extends Model
{
    protected $fillable = [
        'coupon_id',
        'user_id',
        'used'
    ];

    protected $casts = [
        'used' => 'boolean'
    ];

    /**
     * Get the user that owns the coupon assignment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the coupon that is assigned to the user.
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}

