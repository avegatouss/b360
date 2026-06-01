<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\Rules\Retail;

use Modules\Eshop360\Pricing\Contracts\PricingRuleInterface;
use Modules\Eshop360\Pricing\DTOs\LineItemPrice;
use Modules\Eshop360\Pricing\DTOs\PricingContext;

/**
 * Applies the product-level discount (percentage or fixed).
 * Runs after the base price has been set.
 */
class DiscountProductRule implements PricingRuleInterface
{
    public function slug(): string
    {
        return 'discount_product';
    }

    public function priority(): int
    {
        return 50;
    }

    public function isApplicable(PricingContext $ctx): bool
    {
        return $ctx->discountType !== null
            && $ctx->discountValue > 0;
    }

    public function apply(PricingContext $ctx, LineItemPrice $current): LineItemPrice
    {
        $discountPerUnit = match ($ctx->discountType) {
            'percentage' => round($current->unitPrice * ($ctx->discountValue / 100), 4),
            'fixed'      => round(min($ctx->discountValue, $current->unitPrice), 4),
            default      => 0.0,
        };

        $totalDiscount = round($discountPerUnit * $ctx->quantity, 4);
        $newTotal      = round($current->total - $totalDiscount, 4);

        return (new LineItemPrice(
            unitPrice:      round($current->unitPrice - $discountPerUnit, 4),
            discountAmount: round($current->discountAmount + $totalDiscount, 4),
            taxAmount:      $current->taxAmount,
            total:          $newTotal,
            appliedRules:   $current->appliedRules,
        ))->withRule($this->slug(), 1, -$totalDiscount);
    }
}
