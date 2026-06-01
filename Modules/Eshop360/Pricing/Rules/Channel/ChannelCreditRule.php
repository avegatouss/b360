<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\Rules\Channel;

use Modules\Billing\Services\FeatureRegistry;
use Modules\Eshop360\Pricing\Contracts\PricingRuleInterface;
use Modules\Eshop360\Pricing\DTOs\LineItemPrice;
use Modules\Eshop360\Pricing\DTOs\PricingContext;

/**
 * Marks that a channel credit (debt) deduction is applicable for this line.
 *
 * This rule does NOT deduct the amount from the total: actual deduction
 * happens at checkout/payment time. It only annotates the LineItemPrice
 * so downstream logic knows credit is in play.
 *
 * Requires the 'channels' feature to be active.
 */
class ChannelCreditRule implements PricingRuleInterface
{
    public function slug(): string
    {
        return 'channel_credit';
    }

    public function priority(): int
    {
        return 60;
    }

    public function isApplicable(PricingContext $ctx): bool
    {
        if (! $ctx->isChannelSale()) {
            return false;
        }

        // Only applicable when debt tracking is enabled via feature flag
        $registry = app(FeatureRegistry::class);

        return $registry->has('eshop360.channels', $ctx->instanceId)
            || $registry->has('channels', $ctx->instanceId);
    }

    public function apply(PricingContext $ctx, LineItemPrice $current): LineItemPrice
    {
        // The debt part was already calculated by ChannelMarginRule.
        // Here we just record that credit is applicable (marker rule).
        // No price modification — actual deduction is at checkout.

        $debtAmount = $current->partDebt ?? 0.0;

        return $current->withRule($this->slug(), 1, $debtAmount);
    }
}
