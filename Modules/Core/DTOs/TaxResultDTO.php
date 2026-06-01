<?php

declare(strict_types=1);

namespace Modules\Core\DTOs;

final readonly class TaxResultDTO
{
    public function __construct(
        public float $amountHt,
        public float $taxAmount,
        public float $amountTtc,
        public float $rate,
        public bool $inclusive,
    ) {}
}
