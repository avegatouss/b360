<?php

namespace Modules\Eshop360\Services\Payment\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Modules\Eshop360\Services\Payment\PaymentGatewayInterface;

final class PinPayDriver implements PaymentGatewayInterface
{
    public function __construct(private array $config = []) {}

    private function baseUrl(): string
    {
        return ($this->config['mode'] ?? 'test') === 'live'
            ? 'https://api.pinpayments.com/1' : 'https://test-api.pinpayments.com/1';
    }

    public function initiate(float $amount, string $currency, array $meta = []): array
    {
        try {
            $response = Http::timeout(30)->withBasicAuth($this->config['secret_key'] ?? '', '')
                ->post($this->baseUrl() . '/charges', [
                    'amount' => (int) ($amount * 100),
                    'currency' => $currency ?: 'AUD',
                    'description' => $meta['description'] ?? 'Payment',
                    'email' => $meta['customer_email'] ?? '',
                    'ip_address' => $meta['ip_address'] ?? request()->ip(),
                    'card_token' => $meta['card_token'] ?? '',
                ]);

            $data = $response->json('response') ?? $response->json();

            if ($response->successful() && isset($data['token'])) {
                return ['success' => true, 'redirect_url' => null, 'transaction_id' => $data['token']];
            }

            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null,
                'error' => $data['error_description'] ?? $response->json('error_description') ?? 'Pin Payments error'];
        } catch (\Throwable $e) {
            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null, 'error' => $e->getMessage()];
        }
    }

    public function verify(string $transactionId): array
    {
        try {
            $response = Http::timeout(15)->withBasicAuth($this->config['secret_key'] ?? '', '')
                ->get($this->baseUrl() . "/charges/{$transactionId}");
            $data = $response->json('response') ?? [];
            $status = ($data['success'] ?? false) ? 'completed' : 'failed';

            return ['success' => $status === 'completed', 'amount' => isset($data['amount']) ? $data['amount'] / 100 : null, 'status' => $status];
        } catch (\Throwable $e) {
            return ['success' => false, 'amount' => null, 'status' => 'unknown', 'error' => $e->getMessage()];
        }
    }

    public function refund(string $transactionId, float $amount): array
    {
        try {
            $response = Http::timeout(15)->withBasicAuth($this->config['secret_key'] ?? '', '')
                ->post($this->baseUrl() . "/charges/{$transactionId}/refunds", ['amount' => (int) ($amount * 100)]);

            $data = $response->json('response') ?? [];

            return isset($data['token'])
                ? ['success' => true, 'refund_id' => $data['token']]
                : ['success' => false, 'refund_id' => null, 'error' => 'Refund failed'];
        } catch (\Throwable $e) {
            return ['success' => false, 'refund_id' => null, 'error' => $e->getMessage()];
        }
    }

    public function handleWebhook(Request $request): array
    {
        $payload = $request->all();
        $data = $payload['data'] ?? [];

        return [
            'valid' => true,
            'transaction_id' => $data['token'] ?? null,
            'status' => ($data['success'] ?? false) ? 'completed' : 'failed',
        ];
    }

    public static function getConfigFields(): array
    {
        return [
            ['name' => 'secret_key', 'label' => 'Secret Key', 'type' => 'password', 'required' => true],
            ['name' => 'mode', 'label' => 'Mode', 'type' => 'select', 'required' => true, 'options' => ['test' => 'Test', 'live' => 'Live']],
        ];
    }
}
