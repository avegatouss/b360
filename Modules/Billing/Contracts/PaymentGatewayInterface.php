<?php

namespace Modules\Billing\Contracts;

interface PaymentGatewayInterface
{
    /** Unique gateway identifier (matches DTO id) */
    public function id(): string;

    /** Human-readable name */
    public function name(): string;

    /** Configure the gateway with credentials from Settings */
    public function configure(array $credentials): void;

    /** Check if gateway is properly configured and ready */
    public function isConfigured(): bool;

    /** Test the connection/credentials. Returns ['success' => bool, 'message' => string] */
    public function testConnection(): array;

    /** Initiate a payment */
    public function initiate(PaymentRequest $request): PaymentResponse;

    /** Verify a webhook/callback from the gateway */
    public function verifyWebhook(array $payload, array $headers = []): WebhookResult;

    /** Check payment status by gateway reference */
    public function checkStatus(string $gatewayReference): PaymentStatus;

    /** List supported currencies */
    public function supportedCurrencies(): array;

    /** Can this gateway handle refunds? */
    public function supportsRefunds(): bool;

    /** Process a refund */
    public function refund(string $gatewayReference, float $amount, string $currency): RefundResult;
}
