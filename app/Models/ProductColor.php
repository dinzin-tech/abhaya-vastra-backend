<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Products; 
use App\Models\ProductVariant; 

class ProductColor extends Model
{
    use HasFactory;

    protected $fillable = ['product_id','color','images'];
    
    protected $casts = [
        'images' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }


    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'color_id');
    }
}
