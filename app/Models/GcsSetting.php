<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GcsSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'storage_driver',
        'gcs_bucket',
        'gcs_project_id',
        'gcs_key_file',
    ];
}
