<?php

namespace Modules\Eshop360\Services;

use Modules\Billing\Services\FeatureRegistry;
use Modules\Core\Support\CurrentInstance;

/**
 * @deprecated Use Modules\Billing\Services\FeatureRegistry instead.
 *
 * This class is kept for backward compatibility. It delegates all calls
 * to the centralized FeatureRegistry in the Billing module.
 *
 * Features are now registered via hooks in Eshop360HooksProvider::registerBillableFeatures().
 */
final class FeatureGate
{
    /**
     * Legacy constants — kept for backward compatibility.
     * The authoritative source is now HookRegistry::features().
     */
    public const FREE_FEATURES = [
        'pos.basic',
        'products.crud',
        'categories.crud',
        'brands.crud',
        'inventory.basic',
        'sales.basic',
        'invoices.basic',
        'customers.crud',
        'suppliers.crud',
        'purchases.basic',
        'reports.basic',
        'expenses.basic',
        'incomes.basic',
        'barcodes',
        'messages',
        'settings.basic',
    ];

    public const PAID_FEATURES = [
        'channels',
        'reports.advanced',
        'reports.export',
        'pdf.invoices',
        'pdf.reports',
        'payment.cinetpay',
        'online_orders',
        'installments',
        'gift_cards',
        'loans',
        'hr',
        'charges',
        'holdings',
        'cash_registers',
        'email_templates',
        'sms',
        'support_tickets',
        'promotions.advanced',
        'quotations',
        'purchase_returns',
        'stock_transfers',
        'multi_warehouse',
        'customer_groups',
        'imports',
        'audit_logs',
        'scheduled_alerts',
        'projects',
    ];

    /**
     * Check if a feature is available for the current instance.
     *
     * Accepts both legacy keys (e.g. 'channels') and new prefixed keys (e.g. 'eshop360.channels').
     */
    public function has(string $feature): bool
    {
        $feature = $this->normalizeFeature($feature);

        $instance = CurrentInstance::get();
        if (!$instance) {
            return in_array($feature, self::FREE_FEATURES, true);
        }

        $registry = app(FeatureRegistry::class);

        // Try exact match first (new format)
        if ($registry->has($feature, $instance->id)) {
            return true;
        }

        // Try with eshop360 prefix (legacy → new format)
        return $registry->has('eshop360.' . $feature, $instance->id);
    }

    /**
     * Check if instance has a paid plan.
     */
    public function isPaid(): bool
    {
        $instance = CurrentInstance::get();
        if (!$instance) {
            return false;
        }

        return app(FeatureRegistry::class)->isPaid($instance->id);
    }

    /**
     * Get available features.
     */
    public function available(): array
    {
        $instance = CurrentInstance::get();
        if (!$instance) {
            return self::FREE_FEATURES;
        }

        return app(FeatureRegistry::class)->available($instance->id);
    }

    /**
     * Get missing paid features.
     */
    public function missing(): array
    {
        $instance = CurrentInstance::get();
        if (!$instance) {
            return self::PAID_FEATURES;
        }

        return app(FeatureRegistry::class)->missing($instance->id)
            ->pluck('id')
            ->all();
    }

    private function normalizeFeature(string $feature): string
    {
        return match ($feature) {
            'payment.inetpay' => 'payment.cinetpay',
            'eshop360.payment.inetpay' => 'eshop360.payment.cinetpay',
            default => $feature,
        };
    }
}
