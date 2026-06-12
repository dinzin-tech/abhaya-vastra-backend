<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderExchange extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'product_id',
        'original_size',
        'original_color',
        'exchange_size',
        'exchange_color',
        'reason',
        'images',
        'exchange_charge',
        'status',
        'payment_id',
        'razorpay_payment_id',
        'razorpay_order_id',
        'payment_status',
        'admin_note',
        'admin_updated_at',
        'shiprocket_pickup_order_id',
        'shiprocket_pickup_shipment_id',
        'shiprocket_pickup_awb_code',
        'shiprocket_pickup_courier_name',
        'pickup_scheduled_at',
        'picked_up_at',
        'shiprocket_delivery_order_id',
        'shiprocket_delivery_shipment_id',
        'shiprocket_delivery_awb_code',
        'shiprocket_delivery_courier_name',
        'delivery_scheduled_at',
        'delivered_at'
    ];

    protected $casts = [
        'images' => 'array',
        'exchange_charge' => 'decimal:2',
        'admin_updated_at' => 'datetime',
        'pickup_scheduled_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivery_scheduled_at' => 'datetime',
        'delivered_at' => 'datetime'
    ];

    /**
     * Get the order that this exchange belongs to
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user that created this exchange
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if exchange is within 7 days of delivery
     */
    public function isWithinExchangePeriod()
    {
        if (!$this->order->delivered_at) {
            // Fallback to created_at if delivered_at not set
            $exchangeDeadline = $this->order->created_at->addDays(7);
            return now()->isBefore($exchangeDeadline);
        }

        $exchangeDeadline = $this->order->delivered_at->addDays(7);
        return now()->isBefore($exchangeDeadline);
    }

    /**
     * Check if the order can be exchanged
     */
    public function canBeExchanged()
    {
        return $this->order->status === 'delivered' && $this->isWithinExchangePeriod();
    }
}
