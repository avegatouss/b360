<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Finance\Exceptions;

use RuntimeException;

/**
 * Numérotation atomique impossible après MAX_NUMBER_ATTEMPTS tentatives.
 *
 * Levée par {@see \Modules\Menuiserie360\Domain\Finance\Services\InvoiceNumberGenerator}
 * en cas de race condition pathologique (très rare en pratique — la première
 * collision UNIQUE déclenche un retry, et 5 tentatives suffisent largement
 * sauf charge anormale ou bug).
 */
final class NumberGenerationFailedException extends RuntimeException
{
    public static function afterAttempts(int $attempts): self
    {
        return new self(
            "InvoiceNumberGenerator: max attempts ({$attempts}) reached without success — possible pathological concurrency or bug."
        );
    }
}
