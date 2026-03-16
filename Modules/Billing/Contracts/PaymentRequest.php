<?php

namespace Modules\Billing\Contracts;

final class PaymentRequest
{
    public function __construct(
        public readonly int $invoiceId,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $description,
        public readonly string $reference,
        public readonly string $callbackUrl,
        public readonly string $returnUrl,
        public readonly ?string $cancelUrl = null,
        public readonly ?string $customerName = null,
        public readonly ?string $customerEmail = null,
        public readonly ?string $customerPhone = null,
        public readonly ?string $paymentMethod = null,
        public readonly array $metadata = [],
    ) {}
}
