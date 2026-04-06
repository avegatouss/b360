<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\Rules\Channel;

use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Pricing\Contracts\PricingRuleInterface;
use Modules\Eshop360\Pricing\DTOs\LineItemPrice;
use Modules\Eshop360\Pricing\DTOs\PricingContext;

/**
 * Sets the initial unit price for a channel sale.
 *
 * Priority: manual override from eshop_channel_product_prices,
 * then fallback to PGHT * buy_rate from the channel config.
 */
class ChannelBasePriceRule implements PricingRuleInterface
{
    public function slug(): string
    {
        return 'channel_base_price';
    }

    public function priority(): int
    {
        return 10;
    }

    public function isApplicable(PricingContext $ctx): bool
    {
        return $ctx->isChannelSale();
    }

    public function apply(PricingContext $ctx, LineItemPrice $current): LineItemPrice
    {
        $unitPrice = $this->resolveChannelPrice($ctx);
        $total     = round($unitPrice * $ctx->quantity, 4);

        return (new LineItemPrice(
            unitPrice:      $unitPrice,
            discountAmount: $current->discountAmount,
            taxAmount:      $current->taxAmount,
            total:          $total,
            appliedRules:   $current->appliedRules,
        ))->withRule($this->slug(), 1, $total);
    }

    /**
     * Resolve the channel sale price for this product.
     *
     * 1. Check eshop_channel_product_prices for an explicit sale_price
     * 2. Fall back to PGHT * (1 + buy_rate) from the channel
     * 3. Last resort: use the product's retail base price
     */
    private function resolveChannelPrice(PricingContext $ctx): float
    {
        // 1. Explicit channel product price
        $channelPrice = (float) DB::table('eshop_channel_product_prices')
            ->where('channel_id', $ctx->channelId)
            ->where('product_id', $ctx->productId)
            ->value('sale_price');

        if ($channelPrice > 0) {
            return round($channelPrice, 4);
        }

        // 2. PGHT * buy_rate
        if ($ctx->pght > 0) {
            $buyRate = (float) DB::table('eshop_distribution_channels')
                ->where('id', $ctx->channelId)
                ->value('buy_rate');

            return round($ctx->pght * (1 + $buyRate), 4);
        }

        // 3. Fallback to retail price
        return round($ctx->basePrice, 4);
    }
}
