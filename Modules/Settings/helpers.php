<?php

if (!function_exists('setting')) {
    function setting(string $key, mixed $default = null, ?int $instanceId = null): mixed
    {
        return app(\Modules\Settings\Services\SettingsManager::class)->get($key, $default, $instanceId);
    }
}
