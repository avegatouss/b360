<?php

declare(strict_types=1);

namespace Modules\Core\DTOs;

final readonly class OrderQuantityLimitsDTO
{
    /**
     * @param  'channel'|'product'|'global'  $source
     */
    public function __construct(
        public int $min,
        public ?int $max,
        public string $source,
    ) {}
}
