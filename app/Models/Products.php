<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Products extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        // 'price',
        // 'discount',
        'main_image',
        'zoomed_image',
        // 'total_price',
        'best_seller',
        'is_featured',
        'customizable',
        'gender',
        'is_qikink_product',
        'qikink_sku',
        'qikink_print_type_id',
        'search_from_my_products'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

     public function colors()
    {
        return $this->hasMany(ProductColor::class, 'product_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id');
    }
}