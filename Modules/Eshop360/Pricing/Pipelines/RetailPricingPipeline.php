<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\Pipelines;

use Modules\Eshop360\Pricing\Contracts\PricingRuleInterface;
use Modules\Eshop360\Pricing\DTOs\LineItemPrice;
use Modules\Eshop360\Pricing\DTOs\PricingContext;

/**
 * Retail pricing pipeline: base price -> product discount -> tax -> guards.
 *
 * Rules are pre-sorted by priority at construction time.
 * Each rule receives the accumulated LineItemPrice and returns a new one.
 */
class RetailPricingPipeline
{
    /** @var PricingRuleInterface[] */
    private readonly array $rules;

    /**
     * @param PricingRuleInterface[] $rules  Already sorted by priority ascending.
     */
    public function __construct(array $rules)
    {
        // Defensive sort in case caller did not pre-sort
        $sorted = $rules;
        usort($sorted, fn (PricingRuleInterface $a, PricingRuleInterface $b) => $a->priority() <=> $b->priority());
        $this->rules = $sorted;
    }

    /**
     * Execute the pipeline, accumulating adjustments into the LineItemPrice.
     */
    public function execute(PricingContext $ctx): LineItemPrice
    {
        $price = new LineItemPrice();

        foreach ($this->rules as $rule) {
            if ($rule->isApplicable($ctx)) {
                $price = $rule->apply($ctx, $price);
            }
        }

        return $price;
    }
}
