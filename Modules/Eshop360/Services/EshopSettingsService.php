<?php

namespace Modules\Eshop360\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\EshopModuleSetting;

class EshopSettingsService
{
    private const CACHE_TTL = 3600; // 1 hour

    private static array $defaults = [
        'general' => [
            'hierarchical_menu' => false,
        ],
        'pos' => [
            'default_layout' => 'layout1',
            'default_warehouse_id' => null,
            'default_customer_id' => null,
            'payment_methods' => ['cash', 'card'],
            'tax_inclusive' => false,
            'sound_enabled' => true,
            'print_receipt' => true,
            'products_per_page' => 24,
            'default_discount' => 0,
            'allow_manual_price' => false,
            'barcode_scanner' => true,
            'register_required' => false,
            'customer_account_enabled' => false,
            'allow_walkin_customer' => true,
        ],
        'printer' => [
            'printer_type' => null,
            'receipt_printer' => '',
            'printer_host' => '',
            'printer_port' => null,
            'printer_share' => '',
            'receipt_width' => 80,
            'receipt_header' => '',
            'receipt_footer' => '',
            'print_logo' => false,
            'logo' => null,
            'auto_print_receipt' => false,
            'print_kitchen_order' => false,
            'kitchen_printer' => '',
            'barcode_printer' => '',
            'barcode_label_width' => 40,
            'barcode_label_height' => 30,
        ],
        'customer' => [
            'allow_multi_user_accounts' => false,
            'customer_portal_enabled' => true,
            'auto_create_user_account' => false,
        ],
        'invoice' => [
            'company_name' => '',
            'company_address' => '',
            'company_phone' => '',
            'company_email' => '',
            'company_logo' => null,
            'tax_number' => '',
            'default_terms' => '',
            'default_footer' => '',
            'default_due_days' => 30,
            'default_template' => 'default',
            'currency_symbol' => 'FCFA',
            'currency_position' => 'before',
            'show_tax_breakdown' => true,
            'show_payment_info' => true,
            'bank_name' => '',
            'bank_account' => '',
            'bank_iban' => '',
        ],
        'channel_branding' => [
            'company_name' => '',
            'company_address' => '',
            'company_phone' => '',
            'company_email' => '',
            'company_logo' => null,
            'tax_number' => '',
            'invoice_header' => '',
            'invoice_footer' => '',
            'receipt_header' => '',
            'receipt_footer' => '',
            'currency_symbol' => 'FCFA',
        ],
        'features' => [
            'portal' => true,
            'shop' => true,
            'orders' => true,
            'online_orders' => true,
            'stock' => true,
            'stock_adjustments' => true,
            'sales' => true,
            'customers' => true,
            'pos' => true,
            'promotions' => true,
            'settings' => true,
            'reports' => true,
            'margins' => true,
            'finance' => false,
            'hr' => false,
            'support' => false,
        ],
    ];

    /**
     * Get settings for a group, with cache + DB persistence.
     */
    public function get(string $group, ?int $instanceId = null): array
    {
        $instanceId ??= CurrentInstance::idOrFail();
        $cacheKey = $this->cacheKey($group, $instanceId);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($group, $instanceId) {
            $record = EshopModuleSetting::where('instance_id', $instanceId)
                ->where('group', $group)
                ->first();

            $defaults = self::$defaults[$group] ?? [];

            return $record ? array_merge($defaults, $record->data) : $defaults;
        });
    }

    /**
     * Save settings for a group (DB + cache).
     */
    public function set(string $group, array $data, ?int $instanceId = null): void
    {
        $instanceId ??= CurrentInstance::idOrFail();

        EshopModuleSetting::updateOrCreate(
            ['instance_id' => $instanceId, 'group' => $group],
            ['data' => $data],
        );

        $cacheKey = $this->cacheKey($group, $instanceId);
        $defaults = self::$defaults[$group] ?? [];
        Cache::put($cacheKey, array_merge($defaults, $data), self::CACHE_TTL);
    }

    /**
     * Get a single value from a settings group.
     */
    public function value(string $group, string $key, mixed $default = null): mixed
    {
        $settings = $this->get($group);

        return $settings[$key] ?? $default;
    }

    /**
     * Get defaults for a group.
     */
    public function defaults(string $group): array
    {
        return self::$defaults[$group] ?? [];
    }

    /**
     * Forget cached settings for a group.
     */
    public function forget(string $group, ?int $instanceId = null): void
    {
        $instanceId ??= CurrentInstance::idOrFail();
        Cache::forget($this->cacheKey($group, $instanceId));
    }

    /**
     * Get settings for a group scoped to a channel, with fallback to instance-level.
     */
    public function getForChannel(string $group, int $channelId, ?int $instanceId = null): array
    {
        $instanceId ??= CurrentInstance::idOrFail();
        $cacheKey = $this->channelCacheKey($group, $instanceId, $channelId);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($group, $instanceId, $channelId) {
            $channelRecord = EshopModuleSetting::where('instance_id', $instanceId)
                ->where('group', $group)
                ->where('channel_id', $channelId)
                ->first();

            if ($channelRecord) {
                $defaults = self::$defaults[$group] ?? [];

                return array_merge($defaults, $channelRecord->data);
            }

            // Fallback to instance-level settings
            return $this->get($group, $instanceId);
        });
    }

    /**
     * Save settings for a group scoped to a channel.
     */
    public function setForChannel(string $group, array $data, int $channelId, ?int $instanceId = null): void
    {
        $instanceId ??= CurrentInstance::idOrFail();

        EshopModuleSetting::updateOrCreate(
            ['instance_id' => $instanceId, 'group' => $group, 'channel_id' => $channelId],
            ['data' => $data],
        );

        $cacheKey = $this->channelCacheKey($group, $instanceId, $channelId);
        $defaults = self::$defaults[$group] ?? [];
        Cache::put($cacheKey, array_merge($defaults, $data), self::CACHE_TTL);
    }

    /**
     * Get a single value from channel-scoped settings.
     */
    public function channelValue(string $group, string $key, int $channelId, mixed $default = null): mixed
    {
        $settings = $this->getForChannel($group, $channelId);

        return $settings[$key] ?? $default;
    }

    /**
     * Forget cached channel settings.
     */
    public function forgetChannel(string $group, int $channelId, ?int $instanceId = null): void
    {
        $instanceId ??= CurrentInstance::idOrFail();
        Cache::forget($this->channelCacheKey($group, $instanceId, $channelId));
    }

    public function isChannelFeatureEnabled(string $feature, int $channelId, bool $default = false): bool
    {
        return (bool) $this->channelValue('features', $feature, $channelId, $default);
    }

    /**
     * Get the branding settings for a channel (logo, company info, PDF headers).
     * Falls back to instance-level invoice settings when not configured.
     */
    public function getChannelBranding(int $channelId): array
    {
        return $this->getForChannel('channel_branding', $channelId);
    }

    /**
     * @param  array<string, bool>  $features
     */
    public function setChannelFeatures(array $features, int $channelId, ?int $instanceId = null): void
    {
        $current = $this->getForChannel('features', $channelId, $instanceId);
        $normalized = [];

        foreach ($features as $key => $value) {
            $normalized[$key] = (bool) $value;
        }

        $this->setForChannel('features', array_merge($current, $normalized), $channelId, $instanceId);
    }

    private function cacheKey(string $group, int $instanceId): string
    {
        return "eshop_settings_{$group}_{$instanceId}";
    }

    private function channelCacheKey(string $group, int $instanceId, int $channelId): string
    {
        return "eshop_settings_{$group}_{$instanceId}_ch_{$channelId}";
    }
}
