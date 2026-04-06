<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\Rules\Channel;

use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Pricing\Contracts\PricingRuleInterface;
use Modules\Eshop360\Pricing\DTOs\ChannelMarginResult;
use Modules\Eshop360\Pricing\DTOs\LineItemPrice;
use Modules\Eshop360\Pricing\DTOs\PricingContext;

/**
 * Calculates the tripartite margin split for a channel sale.
 *
 * margin = (channel_sale_price - PGHT) * quantity
 * Split: owner_share, channel_share, debt_share (from channel config).
 *
 * The margin fields in LineItemPrice become non-null after this rule runs.
 */
class ChannelMarginRule implements PricingRuleInterface
{
    public function slug(): string
    {
        return 'channel_margin';
    }

    public function priority(): int
    {
        return 30;
    }

    public function isApplicable(PricingContext $ctx): bool
    {
        return $ctx->isChannelSale() && $ctx->pght > 0;
    }

    public function apply(PricingContext $ctx, LineItemPrice $current): LineItemPrice
    {
        $margin = $this->calculateMargin($ctx, $current);

        return (new LineItemPrice(
            unitPrice:      $current->unitPrice,
            discountAmount: $current->discountAmount,
            taxAmount:      $current->taxAmount,
            total:          $current->total,
            appliedRules:   $current->appliedRules,
            marginTotal:    $margin->marginTotal,
            partOwner:      $margin->partOwner,
            partChannel:    $margin->partChannel,
            partDebt:       $margin->partDebt,
        ))->withRule($this->slug(), 1, $margin->marginTotal);
    }

    /**
     * Compute the margin result using channel shares.
     */
    private function calculateMargin(PricingContext $ctx, LineItemPrice $current): ChannelMarginResult
    {
        $channel = DB::table('eshop_distribution_channels')
            ->where('id', $ctx->channelId)
            ->first(['owner_share', 'channel_share', 'debt_share']);

        $ownerShare   = (float) ($channel->owner_share   ?? 0);
        $channelShare = (float) ($channel->channel_share ?? 0);
        $debtShare    = (float) ($channel->debt_share    ?? 0);

        // Per-unit margin = sale price - purchase (PGHT) price
        $marginPerUnit = $current->unitPrice - $ctx->pght;
        $marginTotal   = round($marginPerUnit * $ctx->quantity, 4);

        // Check if debt is enabled for this specific channel-product
        $debtEnabled = (bool) DB::table('eshop_channel_product_prices')
            ->where('channel_id', $ctx->channelId)
            ->where('product_id', $ctx->productId)
            ->value('debt_enabled');

        // If debt is not enabled, redistribute the debt share to owner
        $effectiveDebtShare  = $debtEnabled ? $debtShare : 0;
        $effectiveOwnerShare = $debtEnabled ? $ownerShare : ($ownerShare + $debtShare);

        $partOwner   = round($marginTotal * $effectiveOwnerShare, 4);
        $partChannel = round($marginTotal * $channelShare, 4);
        $partDebt    = round($marginTotal * $effectiveDebtShare, 4);

        // Calculate percentage margins relative to PGHT
        $purchaseTotal    = $ctx->pght * $ctx->quantity;
        $marginOwnerPct   = $purchaseTotal > 0 ? round(($partOwner / $purchaseTotal) * 100, 2) : 0;
        $marginChannelPct = $purchaseTotal > 0 ? round(($partChannel / $purchaseTotal) * 100, 2) : 0;

        return new ChannelMarginResult(
            purchasePrice:    round($ctx->pght, 4),
            channelPrice:     round($current->unitPrice, 4),
            marginTotal:      $marginTotal,
            partOwner:        $partOwner,
            partChannel:      $partChannel,
            partDebt:         $partDebt,
            marginOwnerPct:   $marginOwnerPct,
            marginChannelPct: $marginChannelPct,
            debtEnabled:      $debtEnabled,
        );
    }
}
