<?php

namespace Modules\Eshop360\Services\Payment\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Modules\Eshop360\Services\Payment\PaymentGatewayInterface;

final class SecurePayDriver implements PaymentGatewayInterface
{
    private const BASE = 'https://payments.auspost.net.au/v2';

    public function __construct(private array $config = []) {}

    public function initiate(float $amount, string $currency, array $meta = []): array
    {
        try {
            $response = Http::timeout(30)
                ->withBasicAuth($this->config['merchant_id'] ?? '', $this->config['password'] ?? '')
                ->post(self::BASE . '/orders', [
                    'amount' => (int) ($amount * 100),
                    'currency' => $currency ?: 'AUD',
                    'merchantOrderId' => $meta['reference'] ?? uniqid('SP-'),
                    'redirectUrls' => [
                        'successUrl' => $meta['return_url'] ?? '',
                        'failedUrl' => $meta['cancel_url'] ?? $meta['return_url'] ?? '',
                    ],
                ]);

            $data = $response->json();

            if ($response->successful() && isset($data['orderId'])) {
                return ['success' => true, 'redirect_url' => $data['paymentUrl'] ?? null, 'transaction_id' => $data['orderId']];
            }

            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null, 'error' => $data['message'] ?? 'SecurePay error'];
        } catch (\Throwable $e) {
            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null, 'error' => $e->getMessage()];
        }
    }

    public function verify(string $transactionId): array
    {
        try {
            $response = Http::timeout(15)
                ->withBasicAuth($this->config['merchant_id'] ?? '', $this->config['password'] ?? '')
                ->get(self::BASE . "/orders/{$transactionId}");
            $data = $response->json();
            $status = match ($data['status'] ?? '') {
                'PAID', 'CAPTURED' => 'completed', 'FAILED' => 'failed', default => 'pending',
            };

            return ['success' => $status === 'completed', 'amount' => isset($data['amount']) ? $data['amount'] / 100 : null, 'status' => $status];
        } catch (\Throwable $e) {
            return ['success' => false, 'amount' => null, 'status' => 'unknown', 'error' => $e->getMessage()];
        }
    }

    public function refund(string $transactionId, float $amount): array
    {
        try {
            $response = Http::timeout(15)
                ->withBasicAuth($this->config['merchant_id'] ?? '', $this->config['password'] ?? '')
                ->post(self::BASE . "/orders/{$transactionId}/refunds", ['amount' => (int) ($amount * 100)]);

            return $response->successful()
                ? ['success' => true, 'refund_id' => $response->json('refundId')]
                : ['success' => false, 'refund_id' => null, 'error' => 'Refund failed'];
        } catch (\Throwable $e) {
            return ['success' => false, 'refund_id' => null, 'error' => $e->getMessage()];
        }
    }

    public function handleWebhook(Request $request): array
    {
        $payload = $request->all();

        return [
            'valid' => true,
            'transaction_id' => $payload['orderId'] ?? null,
            'status' => ($payload['status'] ?? '') === 'PAID' ? 'completed' : 'pending',
        ];
    }

    public static function getConfigFields(): array
    {
        return [
            ['name' => 'merchant_id', 'label' => 'Merchant ID', 'type' => 'text', 'required' => true],
            ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true],
        ];
    }
}
