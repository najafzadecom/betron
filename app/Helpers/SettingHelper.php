<?php

use App\Services\SettingService;

if (!function_exists('setting')) {
    /**
     * Get setting value by key
     */
    function setting(string $key, $default = null)
    {
        static $settingService = null;

        if ($settingService === null) {
            $settingService = app(SettingService::class);
        }

        return $settingService->getValue($key, $default);
    }
}

if (!function_exists('setSetting')) {
    /**
     * Set setting value by key
     */
    function setSetting(string $key, $value): bool
    {
        $settingService = app(SettingService::class);

        return $settingService->setValue($key, $value);
    }
}

if (!function_exists('getSettingsByGroup')) {
    /**
     * Get settings by group
     */
    function getSettingsByGroup(string $group): array
    {
        $settingService = app(SettingService::class);
        $settings = $settingService->getByGroup($group);

        return $settings->pluck('value', 'key')->toArray();
    }
}

if (!function_exists('isMaintenanceMode')) {
    /**
     * Check if maintenance mode is enabled
     */
    function isMaintenanceMode(): bool
    {
        return (bool)setting('maintenance_mode', false);
    }
}

if (!function_exists('getSiteName')) {
    /**
     * Get site name
     */
    function getSiteName(): string
    {
        return setting('site_name', 'CMS Panel');
    }
}

if (!function_exists('getSiteDescription')) {
    /**
     * Get site description
     */
    function getSiteDescription(): string
    {
        return setting('site_description', 'İdarəetmə paneli');
    }
}
