<?php

namespace App\Settings;

use App\Models\AppSetting;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;

class CacheSettingsRepository implements SettingsRepositoryInterface
{
    protected bool $supportsTags;

    public function __construct()
    {
        $this->supportsTags = Cache::getStore() instanceof TaggableStore;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = $this->cacheKey($key);

        if (! $this->supportsTags) {
            return AppSetting::where('setting_key', $key)->value('setting_value') ?? $default;
        }

        return $this->cacheStore()
            ->remember($cacheKey, 3600, fn () => AppSetting::where('setting_key', $key)->value('setting_value') ?? $default);
    }

    public function set(string $key, mixed $value): void
    {
        AppSetting::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );

        $this->flushCache($key);
    }

    public function forget(string $key): void
    {
        AppSetting::where('setting_key', $key)->delete();
        $this->flushCache($key);
    }

    public function getGroup(string $prefix): array
    {
        if (! $this->supportsTags) {
            return AppSetting::where('setting_key', 'like', $prefix.'_%')
                ->pluck('setting_value', 'setting_key')
                ->toArray();
        }

        $cacheKey = $this->cacheKey('group:'.$prefix);

        return $this->cacheStore()->remember($cacheKey, 3600, function () use ($prefix) {
            return AppSetting::where('setting_key', 'like', $prefix.'_%')
                ->pluck('setting_value', 'setting_key')
                ->toArray();
        });
    }

    public function clearGroup(string $prefix): void
    {
        $this->flushCache();
    }

    protected function cacheStore()
    {
        return $this->supportsTags ? Cache::tags(['app_settings']) : Cache::store(config('cache.default'));
    }

    protected function flushCache(?string $key = null): void
    {
        if ($this->supportsTags) {
            Cache::tags(['app_settings'])->flush();
            return;
        }

        if ($key) {
            Cache::forget($this->cacheKey($key));
        }
    }

    protected function cacheKey(string $key): string
    {
        return 'app_setting:'.$key;
    }
}
