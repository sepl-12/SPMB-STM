<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'setting_value' => 'string',
    ];

    /**
     * Cache duration in seconds (1 hour)
     */
    const CACHE_DURATION = 3600;

    /**
     * Get setting value by key (with cache)
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = "app_setting_{$key}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($key, $default) {
            $setting = self::where('setting_key', $key)->first();
            return $setting ? $setting->setting_value : $default;
        });
    }

    /**
     * Set setting value by key
     */
    public static function set(string $key, $value): void
    {
        self::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $value,
                'updated_at' => now(),
            ]
        );

        // Clear cache
        Cache::forget("app_setting_{$key}");
    }

    /**
     * Check if setting exists
     */
    public static function has(string $key): bool
    {
        return self::where('setting_key', $key)->exists();
    }

    /**
     * Delete setting
     */
    public static function remove(string $key): bool
    {
        Cache::forget("app_setting_{$key}");
        return self::where('setting_key', $key)->delete();
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        $keys = self::pluck('setting_key');
        foreach ($keys as $key) {
            Cache::forget("app_setting_{$key}");
        }
    }

    /**
     * Get all settings as key-value array
     */
    public static function allSettings(): array
    {
        return self::query()
            ->pluck('setting_value', 'setting_key')
            ->toArray();
    }

    /**
     * Get settings by group (prefix)
     * Example: getGroup('contact') returns all contact_* settings
     */
    public static function getGroup(string $prefix): array
    {
        return self::query()
            ->where('setting_key', 'like', $prefix . '_%')
            ->pluck('setting_value', 'setting_key')
            ->toArray();
    }
}
