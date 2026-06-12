<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customized extends Model
{
    use HasFactory;

    // If you want a different table name, uncomment this
    // protected $table = 'customizeds';

    protected $fillable = [
        'title',
        'description',
        'images',
        'customizable',
        'slug'
    ];

    protected $casts = [
        'images' => 'array',
    ];
}