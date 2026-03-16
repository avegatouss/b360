<?php

namespace Modules\Billing\Contracts;

final class WebhookResult
{
    public function __construct(
        public readonly bool $valid,
        public readonly ?string $gatewayReference = null,
        public readonly ?string $internalReference = null,
        public readonly string $status = 'unknown',
        public readonly ?string $error = null,
        public readonly array $rawPayload = [],
    ) {}
}
