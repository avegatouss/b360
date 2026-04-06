<?php

declare(strict_types=1);

namespace Modules\Core\DTOs;

final readonly class ChannelMarginDTO
{
    public function __construct(
        public int $channelId,
        public int $productId,
        public float $purchasePrice,
        public float $channelPrice,
        public float $marginTotal,
        public float $partOwner,
        public float $partChannel,
        public float $partDebt,
    ) {}
}
