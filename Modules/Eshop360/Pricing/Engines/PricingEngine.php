<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\Engines;

use Modules\Eshop360\Pricing\Cache\PricingCacheManager;
use Modules\Eshop360\Pricing\DTOs\LineItemPrice;
use Modules\Eshop360\Pricing\DTOs\PricingContext;
use Modules\Eshop360\Pricing\DTOs\PricingResult;
use Modules\Eshop360\Pricing\Pipelines\ChannelPricingPipeline;
use Modules\Eshop360\Pricing\Pipelines\RetailPricingPipeline;
use Modules\Eshop360\Pricing\Registry\PricingRuleRegistry;

/**
 * Entry point for all pricing calculations.
 *
 * Selects the appropriate pipeline (retail vs channel), handles caching,
 * and aggregates line results into an order-level PricingResult.
 */
class PricingEngine
{
    public function __construct(
        private readonly PricingRuleRegistry  $registry,
        private readonly PricingCacheManager  $cache,
    ) {}

    /**
     * Calculate the price for a single line item.
     */
    public function calculateLine(PricingContext $ctx): LineItemPrice
    {
        // Build a versioned cache key that auto-invalidates on product price change
        $baseKey     = $ctx->cacheKey();
        $versionedKey = $this->cache->versionedKey($baseKey, $ctx->productId);

        // Skip cache when a coupon is present (unique per-coupon result)
        if (! $ctx->hasCoupon()) {
            $cached = $this->cache->get($versionedKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $result = $ctx->isChannelSale()
            ? $this->runChannelPipeline($ctx)
            : $this->runRetailPipeline($ctx);

        // Cache the result (skip when coupon present)
        if (! $ctx->hasCoupon()) {
            $this->cache->set($versionedKey, $result);
        }

        return $result;
    }

    /**
     * Calculate pricing for an entire order (multiple line items).
     *
     * @param int                       $instanceId
     * @param array<int, array{
     *     productId: int,
     *     basePrice: float,
     *     costPrice: float,
     *     pght: float,
     *     wholesalePrice: float,
     *     taxRate: float,
     *     taxInclusive: bool,
     *     quantity: int,
     *     discountType?: ?string,
     *     discountValue?: float,
     *     customerId?: ?int,
     * }> $items  Each item's pricing data
     * @param ?int    $channelId
     * @param ?string $couponCode
     */
    public function calculateOrder(
        int     $instanceId,
        array   $items,
        ?int    $channelId   = null,
        ?string $couponCode  = null,
    ): PricingResult {
        $lines = [];

        foreach ($items as $item) {
            $ctx = new PricingContext(
                instanceId:    $instanceId,
                productId:     (int) ($item['productId'] ?? 0),
                basePrice:     (float) ($item['basePrice'] ?? 0),
                costPrice:     (float) ($item['costPrice'] ?? 0),
                pght:          (float) ($item['pght'] ?? 0),
                wholesalePrice:(float) ($item['wholesalePrice'] ?? 0),
                taxRate:       (float) ($item['taxRate'] ?? 0),
                taxInclusive:  (bool) ($item['taxInclusive'] ?? false),
                quantity:      (int) ($item['quantity'] ?? 1),
                customerId:    $item['customerId'] ?? null,
                channelId:     $channelId,
                couponCode:    $couponCode,
                discountType:  $item['discountType'] ?? null,
                discountValue: (float) ($item['discountValue'] ?? 0),
            );

            $lines[] = $this->calculateLine($ctx);
        }

        return PricingResult::fromLines($lines);
    }

    /**
     * Run the retail pipeline with the rules active for this instance.
     */
    private function runRetailPipeline(PricingContext $ctx): LineItemPrice
    {
        $rules    = $this->registry->getRulesForRetail($ctx->instanceId);
        $pipeline = new RetailPricingPipeline($rules);

        return $pipeline->execute($ctx);
    }

    /**
     * Run the channel pipeline with the rules active for this instance.
     */
    private function runChannelPipeline(PricingContext $ctx): LineItemPrice
    {
        $rules    = $this->registry->getRulesForChannel($ctx->instanceId);
        $pipeline = new ChannelPricingPipeline($rules);

        return $pipeline->execute($ctx);
    }
}
