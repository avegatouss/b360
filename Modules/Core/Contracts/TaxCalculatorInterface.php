<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use Modules\Core\DTOs\TaxResultDTO;

interface TaxCalculatorInterface
{
    public function calculate(float $amount, float $rate, bool $inclusive = false): TaxResultDTO;

    /**
     * @return array<int, float>
     */
    public function getProductRates(int $productId): array;
}
