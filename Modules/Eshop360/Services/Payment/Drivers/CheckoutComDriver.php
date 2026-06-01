<?php

namespace Modules\Eshop360\Services\Payment\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Modules\Eshop360\Services\Payment\PaymentGatewayInterface;

final class CheckoutComDriver implements PaymentGatewayInterface
{
    private const BASE = 'https://api.checkout.com';

    public function __construct(private array $config = []) {}

    public function initiate(float $amount, string $currency, array $meta = []): array
    {
        try {
            $response = Http::timeout(30)->withToken($this->config['secret_key'] ?? '')
                ->post(self::BASE.'/hosted-payments', [
                    'amount' => (int) ($amount * 100),
                    'currency' => $currency,
                    'reference' => $meta['reference'] ?? uniqid('CKO-'),
                    'description' => $meta['description'] ?? 'Payment',
                    'success_url' => $meta['return_url'] ?? '',
                    'failure_url' => $meta['cancel_url'] ?? $meta['return_url'] ?? '',
                    'customer' => ['email' => $meta['customer_email'] ?? ''],
                ]);

            $data = $response->json();

            if ($response->successful() && isset($data['id'])) {
                return ['success' => true, 'redirect_url' => $data['_links']['redirect']['href'] ?? null, 'transaction_id' => $data['id']];
            }

            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null, 'error' => $data['error_type'] ?? 'Checkout.com error'];
        } catch (\Throwable $e) {
            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null, 'error' => $e->getMessage()];
        }
    }

    public function verify(string $transactionId): array
    {
        try {
            $response = Http::timeout(15)->withToken($this->config['secret_key'] ?? '')
                ->get(self::BASE."/payments/{$transactionId}");
            $data = $response->json();
            $status = match ($data['status'] ?? '') {
                'Captured', 'Paid' => 'completed', 'Declined' => 'failed', default => 'pending',
            };

            return ['success' => $status === 'completed', 'amount' => isset($data['amount']) ? $data['amount'] / 100 : null, 'status' => $status];
        } catch (\Throwable $e) {
            return ['success' => false, 'amount' => null, 'status' => 'unknown', 'error' => $e->getMessage()];
        }
    }

    public function refund(string $transactionId, float $amount): array
    {
        try {
            $response = Http::timeout(15)->withToken($this->config['secret_key'] ?? '')
                ->post(self::BASE."/payments/{$transactionId}/refunds", ['amount' => (int) ($amount * 100)]);
            $data = $response->json();

            return $response->successful()
                ? ['success' => true, 'refund_id' => $data['action_id'] ?? $data['id'] ?? null]
                : ['success' => false, 'refund_id' => null, 'error' => 'Refund failed'];
        } catch (\Throwable $e) {
            return ['success' => false, 'refund_id' => null, 'error' => $e->getMessage()];
        }
    }

    public function handleWebhook(Request $request): array
    {
        $payload = $request->all();
        $type = $payload['type'] ?? '';
        $data = $payload['data'] ?? [];
        $status = match ($type) {
            'payment_captured', 'payment_approved' => 'completed', 'payment_declined' => 'failed', default => 'pending',
        };

        return ['valid' => true, 'transaction_id' => $data['id'] ?? null, 'status' => $status];
    }

    public static function getConfigFields(): array
    {
        return [
            ['name' => 'secret_key', 'label' => 'Secret Key', 'type' => 'password', 'required' => true],
            ['name' => 'public_key', 'label' => 'Public Key', 'type' => 'text', 'required' => true],
        ];
    }
}
