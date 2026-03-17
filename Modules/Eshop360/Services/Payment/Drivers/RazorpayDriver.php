<?php

namespace Modules\Eshop360\Services\Payment\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Modules\Eshop360\Services\Payment\PaymentGatewayInterface;

final class RazorpayDriver implements PaymentGatewayInterface
{
    private const BASE = 'https://api.razorpay.com/v1';

    public function __construct(private array $config = []) {}

    public function initiate(float $amount, string $currency, array $meta = []): array
    {
        try {
            $response = Http::timeout(30)->withBasicAuth($this->config['key_id'] ?? '', $this->config['key_secret'] ?? '')
                ->post(self::BASE . '/orders', [
                    'amount' => (int) ($amount * 100),
                    'currency' => $currency,
                    'receipt' => $meta['reference'] ?? uniqid('RZP-'),
                    'notes' => ['description' => $meta['description'] ?? ''],
                ]);

            $data = $response->json();

            if ($response->successful() && isset($data['id'])) {
                return ['success' => true, 'redirect_url' => null, 'transaction_id' => $data['id'],
                    'razorpay_key' => $this->config['key_id'], 'order_id' => $data['id']];
            }

            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null, 'error' => $data['error']['description'] ?? 'Razorpay error'];
        } catch (\Throwable $e) {
            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null, 'error' => $e->getMessage()];
        }
    }

    public function verify(string $transactionId): array
    {
        try {
            $response = Http::timeout(15)->withBasicAuth($this->config['key_id'] ?? '', $this->config['key_secret'] ?? '')
                ->get(self::BASE . "/orders/{$transactionId}");
            $data = $response->json();
            $status = match ($data['status'] ?? '') {
                'paid' => 'completed', 'attempted' => 'pending', default => $data['status'] ?? 'unknown',
            };

            return ['success' => $status === 'completed', 'amount' => isset($data['amount']) ? $data['amount'] / 100 : null, 'status' => $status];
        } catch (\Throwable $e) {
            return ['success' => false, 'amount' => null, 'status' => 'unknown', 'error' => $e->getMessage()];
        }
    }

    public function refund(string $transactionId, float $amount): array
    {
        try {
            $response = Http::timeout(15)->withBasicAuth($this->config['key_id'] ?? '', $this->config['key_secret'] ?? '')
                ->post(self::BASE . "/payments/{$transactionId}/refund", ['amount' => (int) ($amount * 100)]);
            $data = $response->json();

            return $response->successful() && isset($data['id'])
                ? ['success' => true, 'refund_id' => $data['id']]
                : ['success' => false, 'refund_id' => null, 'error' => $data['error']['description'] ?? 'Refund failed'];
        } catch (\Throwable $e) {
            return ['success' => false, 'refund_id' => null, 'error' => $e->getMessage()];
        }
    }

    public function handleWebhook(Request $request): array
    {
        $payload = $request->all();
        $event = $payload['event'] ?? '';
        $entity = $payload['payload']['payment']['entity'] ?? [];
        $status = match ($event) {
            'payment.captured' => 'completed', 'payment.failed' => 'failed', default => 'pending',
        };

        return ['valid' => true, 'transaction_id' => $entity['order_id'] ?? null, 'status' => $status];
    }

    public static function getConfigFields(): array
    {
        return [
            ['name' => 'key_id', 'label' => 'Key ID', 'type' => 'text', 'required' => true],
            ['name' => 'key_secret', 'label' => 'Key Secret', 'type' => 'password', 'required' => true],
        ];
    }
}
