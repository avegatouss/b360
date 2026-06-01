<?php

declare(strict_types=1);

namespace Modules\Core\Exceptions;

use RuntimeException;

class InsufficientCreditException extends RuntimeException
{
    public function __construct(public readonly int $creditId)
    {
        parent::__construct("Insufficient credit for credit line #{$this->creditId}.");
    }
}
