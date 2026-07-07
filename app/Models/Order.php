<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'items',
        'subtotal',
        'discount',
        'shipping_charge',
        'total',
        'coupon_code',
        'status',
        'payment_method',
        'payment_status',
        'reward_points_earned',
        'reward_points_awarded',
        'cancel_reason',
        'delivered_at',
        'shiprocket_order_id',
        'shiprocket_shipment_id',
        'shiprocket_awb_code',
        'shiprocket_courier_name',
        'shiprocket_tracking_url'
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_charge' => 'decimal:2',
        'total' => 'decimal:2',
        'reward_points_earned' => 'integer',
        'reward_points_awarded' => 'boolean',
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::updated(function ($order) {
            if ($order->isDirty('status')) {
                try {
                    \Illuminate\Support\Facades\Mail::to($order->email)->send(new \App\Mail\OrderStatusChangedMail($order));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to send order status email for Order #{$order->order_number} to {$order->email}: " . $e->getMessage());
                }
            }
        });
    }

   

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment for the order.
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Get the returns for the order.
     */
    public function returns()
    {
        return $this->hasMany(OrderReturn::class);
    }

    /**
     * Get the exchanges for the order.
     */
    public function exchanges()
    {
        return $this->hasMany(OrderExchange::class);
    }

    /**
     * Check if the order can be returned (within 7 days of delivery)
     */
    public function canBeReturned()
    {
        if ($this->status !== 'delivered') {
            return false;
        }

        // If delivered_at is not set (for existing orders), allow return for 7 days from created_at
        if (!$this->delivered_at) {
            $returnDeadline = $this->created_at->addDays(7);
            return now()->isBefore($returnDeadline);
        }

        $returnDeadline = $this->delivered_at->addDays(7);
        return now()->isBefore($returnDeadline);
    }

    /**
     * Check if the order already has a return request
     */
    public function hasReturnRequest()
    {
        return $this->returns()->where('status', '!=', 'rejected')->exists();
    }

    /**
     * Check if the order already has an exchange request
     */
    public function hasExchangeRequest()
    {
        return $this->exchanges()->where('status', '!=', 'rejected')->where('status', '!=', 'cancelled')->exists();
    }

    /**
     * Check if the order can be exchanged (within 7 days of delivery)
     */
    public function canBeExchanged()
    {
        if ($this->status !== 'delivered') {
            return false;
        }

        // If delivered_at is not set (for existing orders), allow exchange for 7 days from created_at
        if (!$this->delivered_at) {
            $exchangeDeadline = $this->created_at->addDays(7);
            return now()->isBefore($exchangeDeadline);
        }

        $exchangeDeadline = $this->delivered_at->addDays(7);
        return now()->isBefore($exchangeDeadline);
    }
}
