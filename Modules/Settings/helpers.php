<?php

if (!function_exists('setting')) {
    function setting(string $key, mixed $default = null, ?int $instanceId = null): mixed
    {
        return app(\Modules\Settings\Services\SettingsManager::class)->get($key, $default, $instanceId);
    }
}

if (!function_exists('currency')) {
    /**
     * Resolve currency for the current context.
     * If Currency module is active, it takes priority over billing settings.
     * Priority: CurrencyManager (if module active) → instance billing.currency → root billing.currency → config.
     */
    function currency(?int $instanceId = null): string
    {
        // If Currency module is active, delegate to CurrencyManager
        if (class_exists(\Modules\Currency\Services\CurrencyManager::class)) {
            try {
                return app(\Modules\Currency\Services\CurrencyManager::class)->resolve($instanceId);
            } catch (\Throwable) {
                // Fallback if module not fully booted
            }
        }

        if ($instanceId === null) {
            $current = \Modules\Core\Support\CurrentInstance::get();
            $instanceId = $current?->id;
        }

        return setting('billing.currency', config('billing.currency', 'EUR'), $instanceId);
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format an amount with the resolved currency.
     */
    function format_currency(float $amount, ?string $code = null): string
    {
        if (class_exists(\Modules\Currency\Services\CurrencyManager::class)) {
            try {
                return app(\Modules\Currency\Services\CurrencyManager::class)->format($amount, $code);
            } catch (\Throwable) {
                // Fallback
            }
        }

        $code = $code ?? currency();
        return number_format($amount, 2) . ' ' . $code;
    }
}
