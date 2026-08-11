<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartCouponLog extends Model
{
    protected $fillable = [
        'email',
        'coupon_id',
        'custom_message',
        'sent_at'
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }
}
