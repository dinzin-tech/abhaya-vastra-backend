<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointsConfig extends Model
{
    //
    protected $fillable = [
        'min_amount',
        'max_amount',
        'points',
        'coin_value',
        'status'
    ];
}
