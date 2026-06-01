<?php

namespace Modules\Eshop360\Services\Payment;

use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Initiate a payment.
     *
     * @return array{success: bool, redirect_url: ?string, transaction_id: ?string, error: ?string}
     */
    public function initiate(float $amount, string $currency, array $meta = []): array;

    /**
     * Verify a payment by transaction ID.
     *
     * @return array{success: bool, amount: ?float, status: string, error: ?string}
     */
    public function verify(string $transactionId): array;

    /**
     * Refund a payment.
     *
     * @return array{success: bool, refund_id: ?string, error: ?string}
     */
    public function refund(string $transactionId, float $amount): array;

    /**
     * Handle an incoming webhook from the gateway.
     *
     * @return array{valid: bool, transaction_id: ?string, status: ?string, error: ?string}
     */
    public function handleWebhook(Request $request): array;

    /**
     * Return config field definitions for the setup UI.
     *
     * Each entry: ['name' => string, 'label' => string, 'type' => 'text'|'password'|'select', 'required' => bool, 'options' => array]
     */
    public static function getConfigFields(): array;
}
