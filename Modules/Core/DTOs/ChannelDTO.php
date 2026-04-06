<?php

declare(strict_types=1);

namespace Modules\Core\DTOs;

final readonly class ChannelDTO
{
    public function __construct(
        public int $id,
        public int $instanceId,
        public string $name,
        public string $slug,
        public float $marginRate,
        public float $buyRate,
        public float $debtShare,
        public float $channelShare,
        public float $ownerShare,
        public bool $isActive,
    ) {}
}
