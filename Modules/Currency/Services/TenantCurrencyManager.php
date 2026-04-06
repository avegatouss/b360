<?php

namespace Modules\Currency\Services;

use Modules\Currency\Models\TenantCurrencySetting;
use Modules\Currency\Models\UserCurrencyPreference;

/**
 * Manages per-tenant and per-user currency configuration.
 *
 * Resolution order for display currency:
 * 1. Session override (ad-hoc choice for current request)
 * 2. User preference (persistent per user per instance)
 * 3. Tenant default currency
 * 4. Global fallback: XOF
 */
final class TenantCurrencyManager
{
    /**
     * Get the default currency for a tenant.
     */
    public function getDefault(int $instanceId): string
    {
        return TenantCurrencySetting::forInstance($instanceId)->default_currency;
    }

    /**
     * Get allowed currencies for a tenant.
     *
     * @return string[]
     */
    public function getAllowed(int $instanceId): array
    {
        return TenantCurrencySetting::forInstance($instanceId)->allowed_currencies;
    }

    /**
     * Check if multi-currency is enabled for a tenant.
     */
    public function isMultiCurrencyEnabled(int $instanceId): bool
    {
        return TenantCurrencySetting::forInstance($instanceId)->multi_currency_enabled;
    }

    /**
     * Resolve the display currency for a user in a specific instance.
     */
    public function resolveDisplayCurrency(int $instanceId, ?int $userId = null): string
    {
        // 1. Session override (ad-hoc)
        $sessionKey = "preferred_currency_{$instanceId}";
        $sessionCurrency = session($sessionKey);
        if ($sessionCurrency) {
            return $sessionCurrency;
        }

        // 2. User preference (persistent)
        if ($userId) {
            $pref = UserCurrencyPreference::where('user_id', $userId)
                ->where('instance_id', $instanceId)
                ->first();

            if ($pref) {
                return $pref->preferred_currency;
            }
        }

        // 3. Tenant default
        return $this->getDefault($instanceId);
    }

    /**
     * Set the persistent currency preference for a user.
     */
    public function setUserPreference(int $userId, int $instanceId, string $currencyCode): void
    {
        UserCurrencyPreference::updateOrCreate(
            ['user_id' => $userId, 'instance_id' => $instanceId],
            ['preferred_currency' => $currencyCode]
        );
    }

    /**
     * Set a session-level currency override (ad-hoc, not persistent).
     */
    public function setSessionCurrency(int $instanceId, string $currencyCode): void
    {
        session(["preferred_currency_{$instanceId}" => $currencyCode]);
    }

    /**
     * Update tenant-level currency settings.
     */
    public function updateSettings(int $instanceId, array $data): TenantCurrencySetting
    {
        $settings = TenantCurrencySetting::forInstance($instanceId);
        $settings->update($data);

        return $settings;
    }
}
