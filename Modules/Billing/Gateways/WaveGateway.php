<?php

namespace Modules\Billing\Gateways;

use Illuminate\Support\Facades\Http;
use Modules\Billing\Contracts\PaymentGatewayInterface;
use Modules\Billing\Contracts\PaymentRequest;
use Modules\Billing\Contracts\PaymentResponse;
use Modules\Billing\Contracts\PaymentStatus;
use Modules\Billing\Contracts\RefundResult;
use Modules\Billing\Contracts\WebhookResult;

/**
 * Wave payment gateway — Senegal, Cote d'Ivoire.
 * Uses Wave Business Checkout API.
 *
 * @see https://docs.wave.com/
 */
final class WaveGateway implements PaymentGatewayInterface
{
    private const BASE_URL = 'https://api.wave.com/v1';

    private string $apiKey = '';
    private string $webhookSecret = '';
    private string $businessId = '';

    public function id(): string { return 'wave'; }

    public function name(): string { return 'Wave'; }

    public function configure(array $credentials): void
    {
        $this->apiKey = $credentials['api_key'] ?? '';
        $this->webhookSecret = $credentials['webhook_secret'] ?? '';
        $this->businessId = $credentials['business_id'] ?? '';
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '' && $this->businessId !== '';
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Identifiants Wave manquants.'];
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->apiKey)
                ->get(self::BASE_URL . '/business/' . $this->businessId);

            return $response->successful()
                ? ['success' => true, 'message' => 'Connexion Wave OK.']
                : ['success' => false, 'message' => 'Erreur Wave : ' . $response->status()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function initiate(PaymentRequest $request): PaymentResponse
    {
        try {
            $response = Http::timeout(30)
                ->withToken($this->apiKey)
                ->post(self::BASE_URL . '/checkout/sessions', [
                    'amount' => (string) (int) $request->amount,
                    'currency' => $request->currency,
                    'client_reference' => $request->reference,
                    'error_url' => $request->cancelUrl ?? $request->returnUrl,
                    'success_url' => $request->returnUrl,
                ]);

            $data = $response->json();

            if ($response->successful() && isset($data['id'])) {
                return new PaymentResponse(
                    success: true,
                    gatewayReference: $data['id'],
                    redirectUrl: $data['wave_launch_url'] ?? $data['checkout_url'] ?? null,
                    qrCode: $data['qr_code_url'] ?? null,
                    status: 'pending',
                    metadata: $data,
                );
            }

            return new PaymentResponse(
                success: false,
                error: $data['message'] ?? 'Erreur Wave inconnue.',
                metadata: $data ?? [],
            );
        } catch (\Throwable $e) {
            return new PaymentResponse(success: false, error: $e->getMessage());
        }
    }

    public function verifyWebhook(array $payload, array $headers = []): WebhookResult
    {
        // Verify HMAC signature
        $signature = $headers['wave-signature'] ?? $headers['Wave-Signature'] ?? '';
        if ($this->webhookSecret !== '' && $signature !== '') {
            $expected = hash_hmac('sha256', json_encode($payload), $this->webhookSecret);
            if (!hash_equals($expected, $signature)) {
                return new WebhookResult(valid: false, error: 'Invalid Wave webhook signature.');
            }
        }

        $type = $payload['type'] ?? '';
        $data = $payload['data'] ?? [];

        return new WebhookResult(
            valid: true,
            gatewayReference: $data['id'] ?? null,
            internalReference: $data['client_reference'] ?? null,
            status: match ($type) {
                'checkout.session.completed' => 'completed',
                'checkout.session.expired' => 'cancelled',
                default => 'pending',
            },
            rawPayload: $payload,
        );
    }

    public function checkStatus(string $gatewayReference): PaymentStatus
    {
        try {
            $response = Http::timeout(15)
                ->withToken($this->apiKey)
                ->get(self::BASE_URL . "/checkout/sessions/{$gatewayReference}");

            $data = $response->json();

            return new PaymentStatus(
                status: match ($data['payment_status'] ?? '') {
                    'succeeded' => 'completed',
                    'failed' => 'failed',
                    'expired' => 'cancelled',
                    default => 'pending',
                },
                gatewayReference: $gatewayReference,
                amount: isset($data['amount']) ? (float) $data['amount'] : null,
                currency: $data['currency'] ?? null,
                metadata: $data,
            );
        } catch (\Throwable) {
            return new PaymentStatus(status: 'unknown', gatewayReference: $gatewayReference);
        }
    }

    public function supportedCurrencies(): array { return ['XOF']; }

    public function supportsRefunds(): bool { return false; }

    public function refund(string $gatewayReference, float $amount, string $currency): RefundResult
    {
        return new RefundResult(success: false, error: 'Wave ne supporte pas les remboursements via API.');
    }
}
