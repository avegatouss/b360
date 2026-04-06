<?php

declare(strict_types=1);

namespace Modules\Core\DTOs;

final readonly class ProductDTO
{
    public function __construct(
        public int $id,
        public int $instanceId,
        public string $name,
        public string $sku,
        public float $price,
        public float $costPrice,
        public bool $isActive,
    ) {}
}
