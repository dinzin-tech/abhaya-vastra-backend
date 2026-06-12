<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderReturn extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'reason',
        'images',
        'tracking_id',
        'status',
        'delivered_at',
        'admin_note',
        'admin_updated_at',
        'refund_processed',
        'refund_amount',
        'refund_id',
        'refund_received_at',
        'shiprocket_return_order_id',
        'shiprocket_return_shipment_id',
        'shiprocket_return_awb_code',
        'shiprocket_return_courier_name',
        'return_pickup_scheduled_at'
    ];

    protected $casts = [
        'images' => 'array',
        'delivered_at' => 'datetime',
        'admin_updated_at' => 'datetime',
        'refund_received_at' => 'datetime',
        'return_pickup_scheduled_at' => 'datetime'
    ];

    /**
     * Get the order that this return belongs to
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user that created this return
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if return is within 7 days of delivery
     */
    public function isWithinReturnPeriod()
    {
        if (!$this->order->delivered_at) {
            return false;
        }

        $returnDeadline = $this->order->delivered_at->addDays(7);
        return now()->isBefore($returnDeadline);
    }

    /**
     * Check if the order status allows return
     */
    public function canBeReturned()
    {
        return $this->order->status === 'delivered' && $this->isWithinReturnPeriod();
    }
}
