<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use Modules\Core\Exceptions\InsufficientStockException;

interface StockManagerInterface
{
    /**
     * @throws InsufficientStockException
     */
    public function deduct(int $productId, int $warehouseId, float $qty, string $ref): void;

    public function add(int $productId, int $warehouseId, float $qty, string $ref, string $type = 'in'): void;

    public function getAvailable(int $productId, int $warehouseId): float;

    public function reserve(int $productId, int $warehouseId, float $qty, string $ref): void;

    public function release(int $productId, int $warehouseId, float $qty, string $ref): void;
}
