<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\Exceptions;

use InvalidArgumentException;

class InvalidOrderQuantityException extends InvalidArgumentException
{
    public static function belowMinimum(int $quantity, int $min, string $source): self
    {
        return new self(sprintf(
            'Order quantity %d is below the minimum of %d (source: %s).',
            $quantity,
            $min,
            $source,
        ));
    }

    public static function aboveMaximum(int $quantity, int $max, string $source): self
    {
        return new self(sprintf(
            'Order quantity %d exceeds the maximum of %d (source: %s).',
            $quantity,
            $max,
            $source,
        ));
    }
}
