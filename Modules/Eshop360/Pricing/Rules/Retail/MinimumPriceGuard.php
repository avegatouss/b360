<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\Rules\Retail;

use Modules\Eshop360\Pricing\Contracts\PricingRuleInterface;
use Modules\Eshop360\Pricing\DTOs\LineItemPrice;
use Modules\Eshop360\Pricing\DTOs\PricingContext;

/**
 * Safety guard: ensures the line total never goes below zero.
 * Should run last in every pipeline.
 */
class MinimumPriceGuard implements PricingRuleInterface
{
    public function slug(): string
    {
        return 'minimum_price_guard';
    }

    public function priority(): int
    {
        return 90;
    }

    public function isApplicable(PricingContext $ctx): bool
    {
        // Always runs as a final safety check
        return true;
    }

    public function apply(PricingContext $ctx, LineItemPrice $current): LineItemPrice
    {
        if ($current->total >= 0) {
            // Nothing to correct
            return $current->withRule($this->slug(), 1, 0.0);
        }

        // Clamp total to zero and adjust the discount accordingly
        $correction     = abs($current->total);
        $newDiscount    = round($current->discountAmount - $correction, 4);
        $newUnitPrice   = $current->unitPrice;

        return (new LineItemPrice(
            unitPrice:      $newUnitPrice,
            discountAmount: max(0, $newDiscount),
            taxAmount:      $current->taxAmount,
            total:          0.0,
            appliedRules:   $current->appliedRules,
            marginTotal:    $current->marginTotal,
            partOwner:      $current->partOwner,
            partChannel:    $current->partChannel,
            partDebt:       $current->partDebt,
        ))->withRule($this->slug(), 1, $correction);
    }
}
