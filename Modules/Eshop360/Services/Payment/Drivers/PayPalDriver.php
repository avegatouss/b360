<?php

namespace Modules\Eshop360\Services\Payment\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Modules\Eshop360\Services\Payment\PaymentGatewayInterface;

final class PayPalDriver implements PaymentGatewayInterface
{
    public function __construct(private array $config = []) {}

    private function baseUrl(): string
    {
        return ($this->config['mode'] ?? 'sandbox') === 'live'
            ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }

    private function accessToken(): ?string
    {
        $response = Http::timeout(15)->withBasicAuth($this->config['client_id'] ?? '', $this->config['client_secret'] ?? '')
            ->asForm()->post($this->baseUrl() . '/v1/oauth2/token', ['grant_type' => 'client_credentials']);

        return $response->successful() ? $response->json('access_token') : null;
    }

    public function initiate(float $amount, string $currency, array $meta = []): array
    {
        try {
            $token = $this->accessToken();
            if (!$token) {
                return ['success' => false, 'redirect_url' => null, 'transaction_id' => null, 'error' => 'PayPal auth failed'];
            }

            $response = Http::timeout(30)->withToken($token)->post($this->baseUrl() . '/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $meta['reference'] ?? uniqid('PP-'),
                    'amount' => ['currency_code' => $currency, 'value' => number_format($amount, 2, '.', '')],
                ]],
                'application_context' => [
                    'return_url' => $meta['return_url'] ?? '',
                    'cancel_url' => $meta['cancel_url'] ?? $meta['return_url'] ?? '',
                ],
            ]);

            $data = $response->json();
            $approveLink = collect($data['links'] ?? [])->firstWhere('rel', 'approve');

            if ($response->successful() && isset($data['id'])) {
                return ['success' => true, 'redirect_url' => $approveLink['href'] ?? null, 'transaction_id' => $data['id']];
            }

            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null, 'error' => $data['message'] ?? 'PayPal error'];
        } catch (\Throwable $e) {
            return ['success' => false, 'redirect_url' => null, 'transaction_id' => null, 'error' => $e->getMessage()];
        }
    }

    public function verify(string $transactionId): array
    {
        try {
            $token = $this->accessToken();
            $response = Http::timeout(15)->withToken($token)->get($this->baseUrl() . "/v2/checkout/orders/{$transactionId}");
            $data = $response->json();
            $status = match ($data['status'] ?? '') {
                'COMPLETED', 'APPROVED' => 'completed', 'VOIDED' => 'failed', default => 'pending',
            };

            return ['success' => $status === 'completed', 'amount' => (float) ($data['purchase_units'][0]['amount']['value'] ?? 0), 'status' => $status];
        } catch (\Throwable $e) {
            return ['success' => false, 'amount' => null, 'status' => 'unknown', 'error' => $e->getMessage()];
        }
    }

    public function refund(string $transactionId, float $amount): array
    {
        try {
            $token = $this->accessToken();
            // Capture first, then refund the capture
            $capture = Http::timeout(15)->withToken($token)->post($this->baseUrl() . "/v2/checkout/orders/{$transactionId}/capture");
            $captureId = $capture->json('purchase_units.0.payments.captures.0.id');

            if (!$captureId) {
                return ['success' => false, 'refund_id' => null, 'error' => 'No capture found'];
            }

            $response = Http::timeout(15)->withToken($token)->post($this->baseUrl() . "/v2/payments/captures/{$captureId}/refund", [
                'amount' => ['currency_code' => 'USD', 'value' => number_format($amount, 2, '.', '')],
            ]);

            return $response->successful()
                ? ['success' => true, 'refund_id' => $response->json('id')]
                : ['success' => false, 'refund_id' => null, 'error' => 'Refund failed'];
        } catch (\Throwable $e) {
            return ['success' => false, 'refund_id' => null, 'error' => $e->getMessage()];
        }
    }

    public function handleWebhook(Request $request): array
    {
        $payload = $request->all();
        $eventType = $payload['event_type'] ?? '';
        $resource = $payload['resource'] ?? [];
        $status = match ($eventType) {
            'CHECKOUT.ORDER.APPROVED', 'PAYMENT.CAPTURE.COMPLETED' => 'completed',
            'PAYMENT.CAPTURE.DENIED' => 'failed',
            default => 'pending',
        };

        return ['valid' => true, 'transaction_id' => $resource['id'] ?? null, 'status' => $status];
    }

    public static function getConfigFields(): array
    {
        return [
            ['name' => 'client_id', 'label' => 'Client ID', 'type' => 'text', 'required' => true],
            ['name' => 'client_secret', 'label' => 'Client Secret', 'type' => 'password', 'required' => true],
            ['name' => 'mode', 'label' => 'Mode', 'type' => 'select', 'required' => true, 'options' => ['sandbox' => 'Sandbox', 'live' => 'Live']],
        ];
    }
}
