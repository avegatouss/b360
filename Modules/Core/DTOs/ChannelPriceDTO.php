<?php

declare(strict_types=1);

namespace Modules\Core\DTOs;

final readonly class ChannelPriceDTO
{
    public function __construct(
        public int $channelId,
        public int $productId,
        public ?float $purchasePrice,
        public ?float $salePrice,
        public float $marginOwnerPct,
        public float $marginChannelPct,
        public bool $debtEnabled,
        public bool $isManualOverride,
        public ?int $minOrderQuantity,
        public ?int $maxOrderQuantity,
    ) {}
}
