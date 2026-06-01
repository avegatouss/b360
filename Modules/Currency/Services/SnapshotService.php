<?php

namespace Modules\Currency\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Currency\Models\OrderCurrencySnapshot;

/**
 * Creates immutable currency snapshots for orders, invoices, and payments.
 *
 * A snapshot freezes the exchange rate at the moment of the transaction.
 * It is NEVER updated — if the rate changes later, the snapshot remains
 * as historical evidence of the rate used.
 */
final class SnapshotService
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRateService,
    ) {}

    /**
     * Create a currency snapshot for any model (order, invoice, payment).
     *
     * @param Model  $entity          The model to attach the snapshot to
     * @param string $displayCurrency The currency the customer sees (e.g., EUR)
     * @param string $baseCurrency    The tenant's base currency (e.g., XOF)
     * @param float  $rate            The exchange rate (display/base)
     */
    public function snapshot(Model $entity, string $displayCurrency, string $baseCurrency, float $rate): OrderCurrencySnapshot
    {
        $total = (float) ($entity->total ?? 0);
        $subtotal = (float) ($entity->subtotal ?? $total);

        $snapshot = OrderCurrencySnapshot::create([
            'snapshotable_type' => get_class($entity),
            'snapshotable_id' => $entity->getKey(),
            'display_currency' => $displayCurrency,
            'base_currency' => $baseCurrency,
            'exchange_rate' => $rate,
            'amounts' => [
                'total_display' => $total,
                'total_base' => $rate > 0 ? round($total / $rate, 4) : $total,
                'subtotal_display' => $subtotal,
                'subtotal_base' => $rate > 0 ? round($subtotal / $rate, 4) : $subtotal,
            ],
            'snapshotted_at' => now(),
        ]);

        // Also write currency_code and exchange_rate on the entity if columns exist
        $updates = [];
        if ($entity->getConnection()->getSchemaBuilder()->hasColumn($entity->getTable(), 'currency_code')) {
            $updates['currency_code'] = $displayCurrency;
        }
        if ($entity->getConnection()->getSchemaBuilder()->hasColumn($entity->getTable(), 'exchange_rate')) {
            $updates['exchange_rate'] = $rate;
        }
        if (!empty($updates)) {
            $entity->update($updates);
        }

        return $snapshot;
    }

    /**
     * Create a snapshot using the current live rate.
     */
    public function snapshotWithCurrentRate(Model $entity, string $displayCurrency, string $baseCurrency): OrderCurrencySnapshot
    {
        $rate = $this->exchangeRateService->getRate($baseCurrency, $displayCurrency);

        return $this->snapshot($entity, $displayCurrency, $baseCurrency, $rate);
    }

    /**
     * Get the snapshot for an entity, if one exists.
     */
    public function getSnapshot(Model $entity): ?OrderCurrencySnapshot
    {
        return OrderCurrencySnapshot::where('snapshotable_type', get_class($entity))
            ->where('snapshotable_id', $entity->getKey())
            ->first();
    }

    /**
     * High-level helper: snapshot only if multi-currency is enabled for the instance.
     *
     * - If the Currency module is not loaded → no-op (BC: works without Currency)
     * - If multi-currency is disabled for the instance → no-op
     * - If display == base currency → just write `currency_code` + `exchange_rate=1.0`
     *   on the entity (no polymorphic snapshot needed)
     * - Otherwise → full snapshot with the current live rate
     *
     * Non-blocking: any failure is logged as a warning and does not bubble up.
     *
     * @param Model    $entity     The model to snapshot (Order, Invoice, Payment)
     * @param int|null $instanceId Defaults to $entity->instance_id
     * @param int|null $userId     For per-user currency preference resolution
     */
    public function snapshotIfEnabled(Model $entity, ?int $instanceId = null, ?int $userId = null): ?OrderCurrencySnapshot
    {
        try {
            if (!app()->bound(TenantCurrencyManager::class)) {
                return null;
            }

            $tenantManager = app(TenantCurrencyManager::class);
            $instanceId ??= (int) ($entity->instance_id ?? 0);

            if ($instanceId <= 0 || !$tenantManager->isMultiCurrencyEnabled($instanceId)) {
                return null;
            }

            $baseCurrency = $tenantManager->getDefault($instanceId);
            $displayCurrency = $tenantManager->resolveDisplayCurrency($instanceId, $userId);

            if ($displayCurrency === $baseCurrency) {
                // Same currency: write code but no conversion needed
                $entity->update([
                    'currency_code' => $baseCurrency,
                    'exchange_rate' => 1.0,
                ]);

                return null;
            }

            return $this->snapshotWithCurrentRate($entity, $displayCurrency, $baseCurrency);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning(
                'Currency snapshotIfEnabled failed for ' . get_class($entity) . ' #' . $entity->getKey() . ': ' . $e->getMessage()
            );

            return null;
        }
    }
}
