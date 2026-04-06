<?php

declare(strict_types=1);

namespace Modules\Core\DTOs;

use DateTimeImmutable;

final readonly class ChannelCreditDTO
{
    public function __construct(
        public int $id,
        public int $channelId,
        public float $amount,
        public float $usedAmount,
        public float $remainingAmount,
        public string $type,
        public string $status,
        public ?DateTimeImmutable $expiresAt,
    ) {}
}
