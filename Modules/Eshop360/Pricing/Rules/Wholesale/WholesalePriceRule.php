<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\Rules\Wholesale;

use Modules\Eshop360\Pricing\Contracts\PricingRuleInterface;
use Modules\Eshop360\Pricing\DTOs\LineItemPrice;
use Modules\Eshop360\Pricing\DTOs\PricingContext;

/**
 * Calculates the wholesale price from PGHT based on the product's wholesale_price_mode.
 *
 * Modes:
 * - 'percentage' : wholesale = pght * (1 + rate/100)
 * - 'fixed'      : wholesale = pght + rate
 * - 'manual'     : use the context's wholesalePrice as-is
 *
 * This rule has the lowest priority (5) and is used in wholesale pipelines.
 * For the retail pipeline, it only applies when the product has an explicit
 * wholesale price that should override the base price.
 */
class WholesalePriceRule implements PricingRuleInterface
{
    public function slug(): string
    {
        return 'wholesale_price';
    }

    public function priority(): int
    {
        return 5;
    }

    public function isApplicable(PricingContext $ctx): bool
    {
        // Only applicable when PGHT is set and we have a wholesale context
        return $ctx->pght > 0 && $ctx->wholesalePrice >= 0;
    }

    public function apply(PricingContext $ctx, LineItemPrice $current): LineItemPrice
    {
        // Resolve the wholesale price mode from the product DB row
        $product = \Illuminate\Support\Facades\DB::table('eshop_products')
            ->where('id', $ctx->productId)
            ->first(['wholesale_price_mode', 'wholesale_price_rate', 'wholesale_price']);

        if (! $product) {
            return $current->withRule($this->slug(), 1, 0.0);
        }

        $mode = $product->wholesale_price_mode ?? 'manual';
        $rate = (float) ($product->wholesale_price_rate ?? 0);

        $wholesaleUnit = match ($mode) {
            'percentage' => round($ctx->pght * (1 + $rate / 100), 4),
            'fixed'      => round($ctx->pght + $rate, 4),
            default      => round((float) ($product->wholesale_price ?? $ctx->wholesalePrice), 4),
        };

        $total = round($wholesaleUnit * $ctx->quantity, 4);

        return (new LineItemPrice(
            unitPrice:      $wholesaleUnit,
            discountAmount: $current->discountAmount,
            taxAmount:      $current->taxAmount,
            total:          $total,
            appliedRules:   $current->appliedRules,
        ))->withRule($this->slug(), 1, $total);
    }
}
