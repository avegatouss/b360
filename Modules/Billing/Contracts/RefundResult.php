<?php

namespace Modules\Billing\Contracts;

final class RefundResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $refundReference = null,
        public readonly ?float $amount = null,
        public readonly ?string $error = null,
    ) {}
}
