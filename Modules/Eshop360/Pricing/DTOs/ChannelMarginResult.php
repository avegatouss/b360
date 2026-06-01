<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\DTOs;

/**
 * Immutable result for tripartite margin calculation on a channel product.
 */
readonly class ChannelMarginResult
{
    public function __construct(
        public float $purchasePrice,
        public float $channelPrice,
        public float $marginTotal,
        public float $partOwner,
        public float $partChannel,
        public float $partDebt,
        public float $marginOwnerPct,
        public float $marginChannelPct,
        public bool  $debtEnabled,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'purchase_price'     => round($this->purchasePrice, 4),
            'channel_price'      => round($this->channelPrice, 4),
            'margin_total'       => round($this->marginTotal, 4),
            'part_owner'         => round($this->partOwner, 4),
            'part_channel'       => round($this->partChannel, 4),
            'part_debt'          => round($this->partDebt, 4),
            'margin_owner_pct'   => round($this->marginOwnerPct, 2),
            'margin_channel_pct' => round($this->marginChannelPct, 2),
            'debt_enabled'       => $this->debtEnabled,
        ];
    }
}
