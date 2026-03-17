<?php

namespace Modules\Eshop360\Services\Payment\Drivers;

use Illuminate\Http\Request;
use Modules\Eshop360\Services\Payment\PaymentGatewayInterface;

final class PayUMoneyDriver implements PaymentGatewayInterface
{
    public function __construct(private array $config = []) {}

    private function baseUrl(): string
    {
        return ($this->config['mode'] ?? 'sandbox') === 'live'
            ? 'https://secure.payu.in/_payment' : 'https://sandboxsecure.payu.in/_payment';
    }

    public function initiate(float $amount, string $currency, array $meta = []): array
    {
        $txnId = $meta['reference'] ?? 'PAYU-' . uniqid();
        $key = $this->config['merchant_key'] ?? '';
        $salt = $this->config['merchant_salt'] ?? '';

        $hashString = "{$key}|{$txnId}|{$amount}|{$meta['description']}|{$meta['customer_name']}|{$meta['customer_email']}|||||||||||{$salt}";
        $hash = strtolower(hash('sha512', $hashString));

        // PayU uses form redirect — return form data for the view to auto-submit
        return [
            'success' => true,
            'redirect_url' => $this->baseUrl(),
            'transaction_id' => $txnId,
            'form_data' => [
                'key' => $key, 'txnid' => $txnId, 'amount' => $amount,
                'productinfo' => $meta['description'] ?? 'Payment',
                'firstname' => $meta['customer_name'] ?? '',
                'email' => $meta['customer_email'] ?? '',
                'phone' => $meta['customer_phone'] ?? '',
                'surl' => $meta['return_url'] ?? '', 'furl' => $meta['cancel_url'] ?? '',
                'hash' => $hash,
            ],
        ];
    }

    public function verify(string $transactionId): array
    {
        // PayU verification happens via callback parameters
        return ['success' => false, 'amount' => null, 'status' => 'unknown', 'error' => 'Use callback for verification'];
    }

    public function refund(string $transactionId, float $amount): array
    {
        return ['success' => false, 'refund_id' => null, 'error' => 'Use PayU dashboard for refunds'];
    }

    public function handleWebhook(Request $request): array
    {
        $status = $request->input('status');
        $txnId = $request->input('txnid');
        $salt = $this->config['merchant_salt'] ?? '';

        // Reverse hash verification
        $postedHash = $request->input('hash');
        $reverseString = "{$salt}|{$status}|||||||||||{$request->input('email')}|{$request->input('firstname')}|{$request->input('productinfo')}|{$request->input('amount')}|{$txnId}|{$this->config['merchant_key']}";
        $calculatedHash = strtolower(hash('sha512', $reverseString));

        $valid = hash_equals($calculatedHash, $postedHash ?? '');

        return [
            'valid' => $valid,
            'transaction_id' => $txnId,
            'status' => $status === 'success' ? 'completed' : 'failed',
        ];
    }

    public static function getConfigFields(): array
    {
        return [
            ['name' => 'merchant_key', 'label' => 'Merchant Key', 'type' => 'text', 'required' => true],
            ['name' => 'merchant_salt', 'label' => 'Merchant Salt', 'type' => 'password', 'required' => true],
            ['name' => 'mode', 'label' => 'Mode', 'type' => 'select', 'required' => true, 'options' => ['sandbox' => 'Sandbox', 'live' => 'Live']],
        ];
    }
}
