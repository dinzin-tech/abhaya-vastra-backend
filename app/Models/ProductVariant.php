<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Products; 
use App\Models\ProductColor; 

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = ['product_id','color_id','size','stock','price','discount','total_price','weight'];

    public function color()
    {
        return $this->belongsTo(ProductColor::class, 'color_id');
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id'); 
    }
}
