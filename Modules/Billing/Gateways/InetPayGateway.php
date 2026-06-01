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
 * InetPay payment gateway — migrated from Eshop360.
 * Supports mobile money and card payments in West Africa.
 */
final class InetPayGateway implements PaymentGatewayInterface
{
    private string $baseUrl = 'https://api.inetpay.com/v1';
    private string $merchantId = '';
    private string $secretKey = '';

    public function id(): string { return 'inetpay'; }

    public function name(): string { return 'InetPay'; }

    public function configure(array $credentials): void
    {
        $this->baseUrl = $credentials['base_url'] ?? 'https://api.inetpay.com/v1';
        $this->merchantId = $credentials['merchant_id'] ?? '';
        $this->secretKey = $credentials['secret_key'] ?? '';
    }

    public function isConfigured(): bool
    {
        return $this->merchantId !== '' && $this->secretKey !== '';
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Identifiants InetPay manquants.'];
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->secretKey)
                ->get($this->baseUrl . '/merchant/status');

            return $response->successful()
                ? ['success' => true, 'message' => 'Connexion InetPay OK.']
                : ['success' => false, 'message' => 'Erreur InetPay : HTTP ' . $response->status()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function initiate(PaymentRequest $request): PaymentResponse
    {
        try {
            $response = Http::timeout(30)
                ->withToken($this->secretKey)
                ->post($this->baseUrl . '/payments/initiate', [
                    'merchant_id' => $this->merchantId,
                    'amount' => (int) $request->amount,
                    'currency' => $request->currency,
                    'description' => $request->description,
                    'transaction_ref' => $request->reference,
                    'callback_url' => $request->callbackUrl,
                    'return_url' => $request->returnUrl,
                    'payment_method' => $request->paymentMethod ?? 'mobile_money',
                    'customer' => array_filter([
                        'name' => $request->customerName,
                        'email' => $request->customerEmail,
                        'phone' => $request->customerPhone,
                    ]),
                    'metadata' => $request->metadata,
                ]);

            $data = $response->json();

            if ($response->successful() && ($data['status'] ?? '') === 'success') {
                return new PaymentResponse(
                    success: true,
                    gatewayReference: $data['transaction_id'] ?? $request->reference,
                    redirectUrl: $data['payment_url'] ?? null,
                    ussdCode: $data['ussd_code'] ?? null,
                    status: 'pending',
                    metadata: $data,
                );
            }

            return new PaymentResponse(
                success: false,
                error: $data['message'] ?? 'Erreur InetPay inconnue.',
                metadata: $data ?? [],
            );
        } catch (\Throwable $e) {
            return new PaymentResponse(success: false, error: $e->getMessage());
        }
    }

    public function verifyWebhook(array $payload, array $headers = []): WebhookResult
    {
        // Verify HMAC signature
        $signature = $headers['x-inetpay-signature'] ?? $headers['X-InetPay-Signature'] ?? '';

        if ($this->secretKey !== '' && $signature !== '') {
            $expected = hash_hmac('sha256', json_encode($payload), $this->secretKey);
            if (!hash_equals($expected, $signature)) {
                return new WebhookResult(valid: false, error: 'Invalid InetPay webhook signature.');
            }
        }

        $status = $payload['status'] ?? '';
        $transactionId = $payload['transaction_id'] ?? null;
        $transactionRef = $payload['transaction_ref'] ?? null;

        return new WebhookResult(
            valid: $transactionId !== null || $transactionRef !== null,
            gatewayReference: $transactionId,
            internalReference: $transactionRef,
            status: match (strtolower($status)) {
                'success', 'completed' => 'completed',
                'failed' => 'failed',
                'cancelled' => 'cancelled',
                default => 'pending',
            },
            rawPayload: $payload,
        );
    }

    public function checkStatus(string $gatewayReference): PaymentStatus
    {
        try {
            $response = Http::timeout(15)
                ->withToken($this->secretKey)
                ->get($this->baseUrl . "/payments/status/{$gatewayReference}");

            $data = $response->json();

            return new PaymentStatus(
                status: match (strtolower($data['status'] ?? '')) {
                    'success', 'completed' => 'completed',
                    'failed' => 'failed',
                    'cancelled' => 'cancelled',
                    default => 'pending',
                },
                gatewayReference: $gatewayReference,
                amount: isset($data['amount']) ? (float) $data['amount'] : null,
                currency: $data['currency'] ?? null,
                paidAt: $data['completed_at'] ?? null,
                metadata: $data,
            );
        } catch (\Throwable) {
            return new PaymentStatus(status: 'unknown', gatewayReference: $gatewayReference);
        }
    }

    public function supportedCurrencies(): array { return ['XOF', 'XAF']; }

    public function supportsRefunds(): bool { return false; }

    public function refund(string $gatewayReference, float $amount, string $currency): RefundResult
    {
        return new RefundResult(success: false, error: 'InetPay ne supporte pas les remboursements via API.');
    }
}
