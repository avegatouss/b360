<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\Contracts;

use Modules\Eshop360\Pricing\DTOs\LineItemPrice;
use Modules\Eshop360\Pricing\DTOs\PricingContext;

interface PricingRuleInterface
{
    /**
     * Unique identifier for this rule (e.g. 'base_price', 'tax', 'channel_margin').
     */
    public function slug(): string;

    /**
     * Execution priority: 10=base, 50=discounts, 80=tax, 90=guards.
     * Lower values run first.
     */
    public function priority(): int;

    /**
     * Whether this rule should run for the given context.
     */
    public function isApplicable(PricingContext $ctx): bool;

    /**
     * Apply the rule, returning a new immutable LineItemPrice with the adjustment.
     */
    public function apply(PricingContext $ctx, LineItemPrice $current): LineItemPrice;
}
