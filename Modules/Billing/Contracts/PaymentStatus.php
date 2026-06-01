<?php

namespace Modules\Billing\Contracts;

final class PaymentStatus
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $gatewayReference = null,
        public readonly ?float $amount = null,
        public readonly ?string $currency = null,
        public readonly ?string $paidAt = null,
        public readonly array $metadata = [],
    ) {}
}
