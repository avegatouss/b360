<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\Rules\Wholesale;

use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Pricing\Contracts\PricingRuleInterface;
use Modules\Eshop360\Pricing\DTOs\LineItemPrice;
use Modules\Eshop360\Pricing\DTOs\PricingContext;

/**
 * Calculates the pharmacy price from the wholesale price based on the product's pharmacy_price_mode.
 *
 * Modes:
 * - 'percentage' : pharmacy = wholesale * (1 + rate/100)
 * - 'fixed'      : pharmacy = wholesale + rate
 * - 'manual'     : use the stored pharmacy_price as-is
 *
 * Priority 6: runs right after WholesalePriceRule (5) so it can use the computed wholesale price.
 */
class PharmacyPriceRule implements PricingRuleInterface
{
    public function slug(): string
    {
        return 'pharmacy_price';
    }

    public function priority(): int
    {
        return 6;
    }

    public function isApplicable(PricingContext $ctx): bool
    {
        return $ctx->pght > 0;
    }

    public function apply(PricingContext $ctx, LineItemPrice $current): LineItemPrice
    {
        $product = DB::table('eshop_products')
            ->where('id', $ctx->productId)
            ->first(['pharmacy_price_mode', 'pharmacy_price_rate', 'pharmacy_price', 'wholesale_price']);

        if (! $product) {
            return $current->withRule($this->slug(), 1, 0.0);
        }

        $mode = $product->pharmacy_price_mode ?? 'manual';
        $rate = (float) ($product->pharmacy_price_rate ?? 0);

        // Use the wholesale unit price computed by the previous rule,
        // or fall back to the stored wholesale_price.
        $wholesaleBase = $current->unitPrice > 0
            ? $current->unitPrice
            : (float) ($product->wholesale_price ?? 0);

        $pharmacyUnit = match ($mode) {
            'percentage' => round($wholesaleBase * (1 + $rate / 100), 4),
            'fixed'      => round($wholesaleBase + $rate, 4),
            default      => round((float) ($product->pharmacy_price ?? $wholesaleBase), 4),
        };

        $total = round($pharmacyUnit * $ctx->quantity, 4);

        return (new LineItemPrice(
            unitPrice:      $pharmacyUnit,
            discountAmount: $current->discountAmount,
            taxAmount:      $current->taxAmount,
            total:          $total,
            appliedRules:   $current->appliedRules,
        ))->withRule($this->slug(), 1, $total);
    }
}
