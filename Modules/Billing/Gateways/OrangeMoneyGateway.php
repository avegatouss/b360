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
 * Orange Money payment gateway — West/Central Africa.
 * Uses the Orange Money Web Payment API.
 *
 * @see https://developer.orange.com/apis/om-webpay
 */
final class OrangeMoneyGateway implements PaymentGatewayInterface
{
    private const AUTH_URL = 'https://api.orange.com/oauth/v3/token';
    private const BASE_URL = 'https://api.orange.com/orange-money-webpay/dev/v1';

    private string $merchantKey = '';
    private string $authorizationHeader = '';
    private string $returnUrl = '';
    private string $cancelUrl = '';
    private string $notifyUrl = '';

    public function id(): string { return 'orange_money'; }

    public function name(): string { return 'Orange Money'; }

    public function configure(array $credentials): void
    {
        $this->merchantKey = $credentials['merchant_key'] ?? '';
        $this->authorizationHeader = $credentials['authorization_header'] ?? '';
        $this->returnUrl = $credentials['return_url'] ?? '';
        $this->cancelUrl = $credentials['cancel_url'] ?? '';
        $this->notifyUrl = $credentials['notify_url'] ?? '';
    }

    public function isConfigured(): bool
    {
        return $this->merchantKey !== '' && $this->authorizationHeader !== '';
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Identifiants Orange Money manquants.'];
        }

        try {
            $token = $this->getAccessToken();

            return $token
                ? ['success' => true, 'message' => 'Connexion Orange Money OK.']
                : ['success' => false, 'message' => 'Impossible d\'obtenir un token Orange Money.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function initiate(PaymentRequest $request): PaymentResponse
    {
        try {
            $token = $this->getAccessToken();

            if (!$token) {
                return new PaymentResponse(success: false, error: 'Impossible d\'obtenir un token Orange Money.');
            }

            $response = Http::timeout(30)
                ->withToken($token)
                ->post(self::BASE_URL . '/webpayment', [
                    'merchant_key' => $this->merchantKey,
                    'currency' => $request->currency,
                    'order_id' => $request->reference,
                    'amount' => (int) $request->amount,
                    'return_url' => $request->returnUrl,
                    'cancel_url' => $request->cancelUrl ?? $request->returnUrl,
                    'notif_url' => $request->callbackUrl,
                    'lang' => 'fr',
                ]);

            $data = $response->json();
            $status = $data['status'] ?? null;

            if ($status === 201) {
                return new PaymentResponse(
                    success: true,
                    gatewayReference: $data['pay_token'] ?? $request->reference,
                    redirectUrl: $data['payment_url'] ?? null,
                    status: 'pending',
                    metadata: $data,
                );
            }

            return new PaymentResponse(
                success: false,
                error: $data['message'] ?? 'Erreur Orange Money.',
                metadata: $data,
            );
        } catch (\Throwable $e) {
            return new PaymentResponse(success: false, error: $e->getMessage());
        }
    }

    public function verifyWebhook(array $payload, array $headers = []): WebhookResult
    {
        $status = $payload['status'] ?? null;
        $orderId = $payload['order_id'] ?? null;
        $payToken = $payload['pay_token'] ?? null;

        return new WebhookResult(
            valid: $orderId !== null,
            gatewayReference: $payToken,
            internalReference: $orderId,
            status: match ($status) {
                'SUCCESS' => 'completed',
                'FAILED' => 'failed',
                'CANCELLED' => 'cancelled',
                default => 'pending',
            },
            rawPayload: $payload,
        );
    }

    public function checkStatus(string $gatewayReference): PaymentStatus
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::timeout(15)
                ->withToken($token)
                ->post(self::BASE_URL . '/transactionstatus', [
                    'pay_token' => $gatewayReference,
                ]);

            $data = $response->json();

            return new PaymentStatus(
                status: match ($data['status'] ?? '') {
                    'SUCCESS' => 'completed',
                    'FAILED' => 'failed',
                    default => 'pending',
                },
                gatewayReference: $gatewayReference,
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
        return new RefundResult(success: false, error: 'Orange Money ne supporte pas les remboursements via API.');
    }

    private function getAccessToken(): ?string
    {
        $response = Http::timeout(10)
            ->withHeaders(['Authorization' => $this->authorizationHeader])
            ->asForm()
            ->post(self::AUTH_URL, ['grant_type' => 'client_credentials']);

        return $response->json('access_token');
    }
}
