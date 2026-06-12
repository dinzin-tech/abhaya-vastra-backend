<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    //

    protected $fillable = ['key', 'value'];

    /**
     * Get a single setting by key with optional default.
     */
    public static function getSetting(string $key, $default = null)
    {
        // Optionally cache for performance
        return Cache::rememberForever("setting_{$key}", function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    /**
     * Get all settings as an associative array.
     */
    public static function getAllSettings(): array
    {
        return Cache::rememberForever('all_settings', function () {
            return static::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Set or update a single setting.
     */
    public static function setSetting(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting_{$key}");
        Cache::forget('all_settings');
    }

    /**
     * Bulk insert or update multiple settings.
     */
    public static function setMultiple(array $settings): void
    {
        foreach ($settings as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
            Cache::forget("setting_{$key}");
        }
        Cache::forget('all_settings');
    }
}
