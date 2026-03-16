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
 * CinetPay payment gateway — West Africa (CI, SN, CM, BF, ML, TG, BJ, GN, CD, CG).
 * Supports mobile money (Orange, MTN, Moov, Wave) + cards via a single API.
 *
 * @see https://docs.cinetpay.com/
 */
final class CinetPayGateway implements PaymentGatewayInterface
{
    private const BASE_URL = 'https://api-checkout.cinetpay.com/v2';

    private string $apiKey = '';
    private string $siteId = '';
    private string $secretKey = '';

    public function id(): string { return 'cinetpay'; }

    public function name(): string { return 'CinetPay'; }

    public function configure(array $credentials): void
    {
        $this->apiKey = $credentials['api_key'] ?? '';
        $this->siteId = $credentials['site_id'] ?? '';
        $this->secretKey = $credentials['secret_key'] ?? '';
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '' && $this->siteId !== '' && $this->secretKey !== '';
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Identifiants CinetPay manquants.'];
        }

        try {
            $response = Http::timeout(10)->post(self::BASE_URL . '/payment', [
                'apikey' => $this->apiKey,
                'site_id' => $this->siteId,
                'transaction_id' => 'TEST-' . uniqid(),
                'amount' => 100,
                'currency' => 'XOF',
                'description' => 'Test de connexion',
                'notify_url' => 'https://example.com/test',
                'return_url' => 'https://example.com/test',
            ]);

            $data = $response->json();

            if (isset($data['code']) && $data['code'] === '201') {
                return ['success' => true, 'message' => 'Connexion CinetPay OK.'];
            }

            return ['success' => false, 'message' => $data['message'] ?? 'Erreur inconnue CinetPay.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Erreur connexion : ' . $e->getMessage()];
        }
    }

    public function initiate(PaymentRequest $request): PaymentResponse
    {
        try {
            $response = Http::timeout(30)->post(self::BASE_URL . '/payment', [
                'apikey' => $this->apiKey,
                'site_id' => $this->siteId,
                'transaction_id' => $request->reference,
                'amount' => (int) $request->amount,
                'currency' => $request->currency,
                'description' => $request->description,
                'notify_url' => $request->callbackUrl,
                'return_url' => $request->returnUrl,
                'channels' => 'ALL',
                'customer_name' => $request->customerName ?? '',
                'customer_email' => $request->customerEmail ?? '',
                'customer_phone_number' => $request->customerPhone ?? '',
                'metadata' => json_encode($request->metadata),
            ]);

            $data = $response->json();

            if (isset($data['code']) && $data['code'] === '201') {
                return new PaymentResponse(
                    success: true,
                    gatewayReference: $data['data']['payment_token'] ?? $request->reference,
                    redirectUrl: $data['data']['payment_url'] ?? null,
                    status: 'pending',
                    metadata: $data['data'] ?? [],
                );
            }

            return new PaymentResponse(
                success: false,
                error: $data['message'] ?? 'Erreur CinetPay inconnue.',
                metadata: $data,
            );
        } catch (\Throwable $e) {
            return new PaymentResponse(success: false, error: $e->getMessage());
        }
    }

    public function verifyWebhook(array $payload, array $headers = []): WebhookResult
    {
        $transactionId = $payload['cpm_trans_id'] ?? null;

        if (!$transactionId) {
            return new WebhookResult(valid: false, error: 'Missing cpm_trans_id in payload.');
        }

        try {
            $response = Http::timeout(15)->post(self::BASE_URL . '/payment/check', [
                'apikey' => $this->apiKey,
                'site_id' => $this->siteId,
                'transaction_id' => $transactionId,
            ]);

            $data = $response->json();
            $code = $data['code'] ?? '';
            $status = match ($code) {
                '00' => 'completed',
                '600' => 'failed',
                '627' => 'cancelled',
                default => 'pending',
            };

            return new WebhookResult(
                valid: true,
                gatewayReference: $data['data']['payment_token'] ?? null,
                internalReference: $transactionId,
                status: $status,
                rawPayload: $data,
            );
        } catch (\Throwable $e) {
            return new WebhookResult(valid: false, error: $e->getMessage());
        }
    }

    public function checkStatus(string $gatewayReference): PaymentStatus
    {
        try {
            $response = Http::timeout(15)->post(self::BASE_URL . '/payment/check', [
                'apikey' => $this->apiKey,
                'site_id' => $this->siteId,
                'transaction_id' => $gatewayReference,
            ]);

            $data = $response->json();
            $code = $data['code'] ?? '';

            return new PaymentStatus(
                status: match ($code) {
                    '00' => 'completed',
                    '600' => 'failed',
                    '627' => 'cancelled',
                    default => 'pending',
                },
                gatewayReference: $gatewayReference,
                amount: isset($data['data']['amount']) ? (float) $data['data']['amount'] : null,
                currency: $data['data']['currency'] ?? null,
                paidAt: $data['data']['payment_date'] ?? null,
                metadata: $data['data'] ?? [],
            );
        } catch (\Throwable $e) {
            return new PaymentStatus(status: 'unknown', gatewayReference: $gatewayReference);
        }
    }

    public function supportedCurrencies(): array
    {
        return ['XOF', 'XAF', 'GNF', 'USD'];
    }

    public function supportsRefunds(): bool { return false; }

    public function refund(string $gatewayReference, float $amount, string $currency): RefundResult
    {
        return new RefundResult(success: false, error: 'CinetPay ne supporte pas les remboursements via API.');
    }
}
