<?php

declare(strict_types=1);

namespace Modules\Core\DTOs;

final readonly class ProductPricingDTO
{
    public function __construct(
        public int $productId,
        public float $price,
        public float $costPrice,
        public float $pght,
        public float $wholesalePrice,
        public string $wholesalePriceMode,
        public float $wholesalePriceRate,
        public float $pharmacyPrice,
        public string $pharmacyPriceMode,
        public float $pharmacyPriceRate,
        public string $priceMode,
        public float $priceRate,
        public float $taxRate,
        public bool $taxInclusive,
        public int $minOrderQuantity,
        public ?int $maxOrderQuantity,
    ) {}
}
