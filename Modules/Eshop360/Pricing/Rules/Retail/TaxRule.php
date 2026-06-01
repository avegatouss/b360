<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\Rules\Retail;

use Modules\Eshop360\Pricing\Contracts\PricingRuleInterface;
use Modules\Eshop360\Pricing\DTOs\LineItemPrice;
use Modules\Eshop360\Pricing\DTOs\PricingContext;

/**
 * Calculates tax based on the product's tax rate and whether the price is tax-inclusive.
 *
 * - Tax inclusive: total stays the same, taxAmount is extracted from the total.
 * - Tax exclusive: taxAmount is added on top of the current total.
 */
class TaxRule implements PricingRuleInterface
{
    public function slug(): string
    {
        return 'tax';
    }

    public function priority(): int
    {
        return 80;
    }

    public function isApplicable(PricingContext $ctx): bool
    {
        return $ctx->taxRate > 0;
    }

    public function apply(PricingContext $ctx, LineItemPrice $current): LineItemPrice
    {
        $rate = $ctx->taxRate / 100;

        if ($ctx->taxInclusive) {
            // Tax is already included in the total: extract it
            $taxAmount = round($current->total - ($current->total / (1 + $rate)), 4);
            $newTotal  = $current->total; // unchanged
        } else {
            // Tax is added on top
            $taxAmount = round($current->total * $rate, 4);
            $newTotal  = round($current->total + $taxAmount, 4);
        }

        return (new LineItemPrice(
            unitPrice:      $current->unitPrice,
            discountAmount: $current->discountAmount,
            taxAmount:      round($current->taxAmount + $taxAmount, 4),
            total:          $newTotal,
            appliedRules:   $current->appliedRules,
            marginTotal:    $current->marginTotal,
            partOwner:      $current->partOwner,
            partChannel:    $current->partChannel,
            partDebt:       $current->partDebt,
        ))->withRule($this->slug(), 1, $taxAmount);
    }
}
