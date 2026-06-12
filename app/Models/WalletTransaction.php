<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    //

    protected $fillable = [
        'wallet_id',
        'type',
        'points',
        'status',
        'description',
        'reference',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeReversed($query)
    {
        return $query->where('status', 'reversed');
    }
}
