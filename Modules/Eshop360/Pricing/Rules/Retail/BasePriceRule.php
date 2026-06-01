<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\Rules\Retail;

use Modules\Eshop360\Pricing\Contracts\PricingRuleInterface;
use Modules\Eshop360\Pricing\DTOs\LineItemPrice;
use Modules\Eshop360\Pricing\DTOs\PricingContext;

/**
 * Sets the initial unit price and line total from the product's base (retail) price.
 * This is always the first rule to run in the retail pipeline.
 */
class BasePriceRule implements PricingRuleInterface
{
    public function slug(): string
    {
        return 'base_price';
    }

    public function priority(): int
    {
        return 10;
    }

    public function isApplicable(PricingContext $ctx): bool
    {
        // Always applicable: every line needs a base price.
        return true;
    }

    public function apply(PricingContext $ctx, LineItemPrice $current): LineItemPrice
    {
        $unitPrice = round($ctx->basePrice, 4);
        $total     = round($unitPrice * $ctx->quantity, 4);

        return (new LineItemPrice(
            unitPrice:      $unitPrice,
            discountAmount: $current->discountAmount,
            taxAmount:      $current->taxAmount,
            total:          $total,
            appliedRules:   $current->appliedRules,
        ))->withRule($this->slug(), 1, $total);
    }
}
