<?php

namespace Modules\Eshop360\Services\Payment\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Modules\Eshop360\Services\Payment\PaymentGatewayInterface;

final class StripeDriver implements PaymentGatewayInterface
{
    private const BASE = 'https://api.stripe.com/v1';

    public function __construct(private array $config = []) {}

    public function initiate(float $amount, string $currency, array $meta = []): array
    {
        try {
            $response = Http::timeout(30)->withBasicAuth($this->config['secret_key'], '')->asForm()
                ->post(self::BASE . '/checkout/sessions', [
                    'payment_method_types[]' => 'card',
                    'mode' => 'payment',
                    'line_items[0][price_data][currency]' => strtolower($currency),
                    'line_items[0][price_data][product_data][name]' => $meta['description'] ?? 'Paiement',
                    'line_items[0][price_data][unit_amount]' => (int) ($amount * 100),
                    'line_items[0][quantity]' => 1,
                    'success_url' => $meta['return_url'] ?? '',
                    'cancel_url' => $meta['cancel_url'] ?? $meta['return_url'] ?? '',
                    'client_reference_id' => $meta['reference'] ?? '',
                    'customer_email' => $meta['customer_email'] ?? '',
                ]);

            $data = $response->json();

            if ($response->successful() && isset($data['id'])) {
                return ['success' => true, 'redirect_url' => $data['url'] ?? null, 'transaction_id' => $data['id']];
            }

            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null, 'error' => $data['error']['message'] ?? 'Stripe error'];
        } catch (\Throwable $e) {
            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null, 'error' => $e->getMessage()];
        }
    }

    public function verify(string $transactionId): array
    {
        try {
            $response = Http::timeout(15)->withBasicAuth($this->config['secret_key'], '')
                ->get(self::BASE . "/checkout/sessions/{$transactionId}");
            $data = $response->json();
            $status = match ($data['payment_status'] ?? '') {
                'paid' => 'completed', 'unpaid' => 'pending', default => 'unknown',
            };

            return ['success' => $status === 'completed', 'amount' => isset($data['amount_total']) ? $data['amount_total'] / 100 : null, 'status' => $status];
        } catch (\Throwable $e) {
            return ['success' => false, 'amount' => null, 'status' => 'unknown', 'error' => $e->getMessage()];
        }
    }

    public function refund(string $transactionId, float $amount): array
    {
        try {
            $session = Http::timeout(15)->withBasicAuth($this->config['secret_key'], '')
                ->get(self::BASE . "/checkout/sessions/{$transactionId}");
            $pi = $session->json('payment_intent');

            if (!$pi) {
                return ['success' => false, 'refund_id' => null, 'error' => 'Payment intent not found'];
            }

            $response = Http::timeout(15)->withBasicAuth($this->config['secret_key'], '')->asForm()
                ->post(self::BASE . '/refunds', ['payment_intent' => $pi, 'amount' => (int) ($amount * 100)]);
            $data = $response->json();

            if ($response->successful() && isset($data['id'])) {
                return ['success' => true, 'refund_id' => $data['id']];
            }

            return ['success' => false, 'refund_id' => null, 'error' => $data['error']['message'] ?? 'Refund failed'];
        } catch (\Throwable $e) {
            return ['success' => false, 'refund_id' => null, 'error' => $e->getMessage()];
        }
    }

    public function handleWebhook(Request $request): array
    {
        $payload = $request->all();
        $type = $payload['type'] ?? '';
        $object = $payload['data']['object'] ?? [];
        $status = match ($type) {
            'checkout.session.completed' => 'completed',
            'checkout.session.expired' => 'cancelled',
            'payment_intent.payment_failed' => 'failed',
            default => 'pending',
        };

        return ['valid' => true, 'transaction_id' => $object['id'] ?? null, 'status' => $status];
    }

    public static function getConfigFields(): array
    {
        return [
            ['name' => 'secret_key', 'label' => 'Cle secrete', 'type' => 'password', 'required' => true],
            ['name' => 'publishable_key', 'label' => 'Cle publique', 'type' => 'text', 'required' => true],
            ['name' => 'webhook_secret', 'label' => 'Secret webhook', 'type' => 'password', 'required' => false],
        ];
    }
}
