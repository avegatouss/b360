<?php

namespace Modules\Billing\Contracts;

final class PaymentResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $gatewayReference = null,
        public readonly ?string $redirectUrl = null,
        public readonly ?string $ussdCode = null,
        public readonly ?string $qrCode = null,
        public readonly string $status = 'pending',
        public readonly ?string $error = null,
        public readonly array $metadata = [],
    ) {}
}
