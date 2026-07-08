<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintDesign extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'image_path',
        'status',
    ];

    protected $appends = ['image_url'];

    public function category()
    {
        return $this->belongsTo(DesignCategory::class, 'category_id');
    }

    public function getImageUrlAttribute()
    {
        return app(\App\Services\StorageService::class)->getUrl($this->image_path);
    }
}
