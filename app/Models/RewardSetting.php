<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardSetting extends Model
{
    protected $fillable = [
        'min_order_value',
        'reward_base_amount',
        'reward_points',
        'points_value',
        'status',
    ];

    protected $casts = [
        'min_order_value' => 'decimal:2',
        'reward_base_amount' => 'decimal:2',
        'reward_points' => 'integer',
        'points_value' => 'decimal:2',
        'status' => 'boolean',
    ];
}
