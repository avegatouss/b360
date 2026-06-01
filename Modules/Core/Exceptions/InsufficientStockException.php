<?php

declare(strict_types=1);

namespace Modules\Core\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(public readonly int $productId)
    {
        parent::__construct("Insufficient stock for product #{$this->productId}.");
    }
}
