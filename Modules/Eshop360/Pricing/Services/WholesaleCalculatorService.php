<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\Services;

use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Pricing\Cache\PricingCacheManager;
use Modules\Eshop360\Pricing\Events\ProductPricingRecalculated;

/**
 * Recalculates wholesale_price, pharmacy_price, and retail price (if auto mode)
 * when the PGHT changes on a product.
 *
 * Used by import cost finalization, manual PGHT edits, and bulk repricing jobs.
 */
class WholesaleCalculatorService
{
    public function __construct(
        private readonly PricingCacheManager $cache,
    ) {}

    /**
     * Recalculate pricing fields based on current PGHT and mode settings.
     * Returns only the fields that changed (for audit/logging).
     *
     * @return array<string, float> Changed fields with their new values
     */
    public function recalculate(Product $product): array
    {
        $pght = (float) ($product->pght ?? 0);
        $changes = [];

        if ($pght <= 0) {
            return $changes;
        }

        // --- Wholesale price ---
        $wholesaleMode = $product->wholesale_price_mode ?? 'manual';
        $wholesaleRate = (float) ($product->wholesale_price_rate ?? 0);

        $newWholesale = match ($wholesaleMode) {
            'percentage' => round($pght * (1 + $wholesaleRate / 100), 2),
            'fixed' => round($pght + $wholesaleRate, 2),
            default => null, // manual: do not change
        };

        if ($newWholesale !== null && (float) $product->wholesale_price !== $newWholesale) {
            $changes['wholesale_price'] = $newWholesale;
        }

        // --- Pharmacy price ---
        $pharmacyMode = $product->pharmacy_price_mode ?? 'manual';
        $pharmacyRate = (float) ($product->pharmacy_price_rate ?? 0);

        // Base for pharmacy calculation is the (possibly new) wholesale price
        $wholesaleBase = $newWholesale ?? (float) ($product->wholesale_price ?? 0);

        $newPharmacy = match ($pharmacyMode) {
            'percentage' => round($wholesaleBase * (1 + $pharmacyRate / 100), 2),
            'fixed' => round($wholesaleBase + $pharmacyRate, 2),
            default => null,
        };

        if ($newPharmacy !== null && (float) $product->pharmacy_price !== $newPharmacy) {
            $changes['pharmacy_price'] = $newPharmacy;
        }

        // --- Retail price (wholesale-based mode) ---
        $priceMode = $product->price_mode ?? 'manual';
        $priceRate = (float) ($product->price_rate ?? 0);

        if ($priceMode === 'wholesale_based' && $wholesaleBase > 0) {
            $newPrice = round($wholesaleBase * (1 + $priceRate / 100), 2);

            if ((float) $product->price !== $newPrice) {
                $changes['price'] = $newPrice;
            }
        }

        return $changes;
    }

    /**
     * Recalculate, persist changes, and fire the ProductPricingRecalculated event.
     */
    public function recalculateAndSave(Product $product): void
    {
        $changes = $this->recalculate($product);

        if (empty($changes)) {
            return;
        }

        DB::transaction(function () use ($product, $changes) {
            $product->update($changes);
        });

        // Invalidate cached prices for this product
        $this->cache->invalidateForProduct($product->id);

        // Fire domain event so listeners (channel repricing, stock valuation, etc.) can react
        event(new ProductPricingRecalculated(
            productId: $product->id,
            instanceId: (int) $product->instance_id,
            changes: $changes,
        ));
    }
}
