<?php

use App\Settings\SettingsRepositoryInterface;

if (!function_exists('setting')) {
    /**
     * Get application setting value by key
     *
     * @param string $key Setting key (e.g., 'contact_email')
     * @param mixed $default Default value if setting not found
     * @return mixed
     */
    function setting(string $key, $default = null)
    {
        return app(SettingsRepositoryInterface::class)->get($key, $default);
    }
}

if (!function_exists('setting_group')) {
    /**
     * Get group of settings by prefix
     *
     * @param string $prefix Setting prefix (e.g., 'contact' or 'social')
     * @return array
     */
    function setting_group(string $prefix): array
    {
        return app(SettingsRepositoryInterface::class)->getGroup($prefix);
    }
}
