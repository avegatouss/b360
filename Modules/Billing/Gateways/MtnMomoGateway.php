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
 * MTN Mobile Money (MoMo) gateway — Collections API.
 * West/Central Africa (CI, CM, BJ, CG, GH, UG).
 *
 * @see https://momodeveloper.mtn.com/api-documentation/collection/
 */
final class MtnMomoGateway implements PaymentGatewayInterface
{
    private string $subscriptionKey = '';
    private string $apiUser = '';
    private string $apiKey = '';
    private string $environment = 'sandbox'; // sandbox | production
    private string $targetEnvironment = '';
    private string $callbackHost = '';

    public function id(): string { return 'mtn_momo'; }

    public function name(): string { return 'MTN Mobile Money'; }

    public function configure(array $credentials): void
    {
        $this->subscriptionKey = $credentials['subscription_key'] ?? '';
        $this->apiUser = $credentials['api_user'] ?? '';
        $this->apiKey = $credentials['api_key'] ?? '';
        $this->environment = $credentials['environment'] ?? 'sandbox';
        $this->targetEnvironment = $credentials['target_environment'] ?? $this->environment;
        $this->callbackHost = $credentials['callback_host'] ?? '';
    }

    public function isConfigured(): bool
    {
        return $this->subscriptionKey !== '' && $this->apiUser !== '' && $this->apiKey !== '';
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Identifiants MTN MoMo manquants.'];
        }

        try {
            $token = $this->getAccessToken();

            return $token
                ? ['success' => true, 'message' => 'Connexion MTN MoMo OK.']
                : ['success' => false, 'message' => 'Impossible d\'obtenir un token MTN MoMo.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function initiate(PaymentRequest $request): PaymentResponse
    {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                return new PaymentResponse(success: false, error: 'Token MTN MoMo non obtenu.');
            }

            $referenceId = $request->reference;

            $response = Http::timeout(30)
                ->withToken($token)
                ->withHeaders([
                    'X-Reference-Id' => $referenceId,
                    'X-Target-Environment' => $this->targetEnvironment,
                    'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
                    'X-Callback-Url' => $request->callbackUrl,
                ])
                ->post($this->baseUrl() . '/collection/v1_0/requesttopay', [
                    'amount' => (string) (int) $request->amount,
                    'currency' => $request->currency,
                    'externalId' => $referenceId,
                    'payer' => [
                        'partyIdType' => 'MSISDN',
                        'partyId' => $request->customerPhone ?? '',
                    ],
                    'payerMessage' => $request->description,
                    'payeeNote' => 'Invoice #' . $request->invoiceId,
                ]);

            if ($response->status() === 202) {
                return new PaymentResponse(
                    success: true,
                    gatewayReference: $referenceId,
                    status: 'pending',
                    metadata: ['message' => 'Request-to-pay envoye. Le client recevra une notification USSD.'],
                );
            }

            return new PaymentResponse(
                success: false,
                error: 'MTN MoMo erreur : HTTP ' . $response->status(),
                metadata: $response->json() ?? [],
            );
        } catch (\Throwable $e) {
            return new PaymentResponse(success: false, error: $e->getMessage());
        }
    }

    public function verifyWebhook(array $payload, array $headers = []): WebhookResult
    {
        $referenceId = $payload['externalId'] ?? $payload['referenceId'] ?? null;
        $status = $payload['status'] ?? null;

        return new WebhookResult(
            valid: $referenceId !== null,
            gatewayReference: $referenceId,
            internalReference: $referenceId,
            status: match (strtoupper($status ?? '')) {
                'SUCCESSFUL' => 'completed',
                'FAILED' => 'failed',
                'REJECTED' => 'cancelled',
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
                ->withHeaders([
                    'X-Target-Environment' => $this->targetEnvironment,
                    'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
                ])
                ->get($this->baseUrl() . "/collection/v1_0/requesttopay/{$gatewayReference}");

            $data = $response->json();

            return new PaymentStatus(
                status: match (strtoupper($data['status'] ?? '')) {
                    'SUCCESSFUL' => 'completed',
                    'FAILED' => 'failed',
                    'REJECTED' => 'cancelled',
                    'PENDING' => 'pending',
                    default => 'unknown',
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

    public function supportedCurrencies(): array { return ['XOF', 'XAF', 'EUR']; }

    public function supportsRefunds(): bool { return false; }

    public function refund(string $gatewayReference, float $amount, string $currency): RefundResult
    {
        return new RefundResult(success: false, error: 'MTN MoMo Collections ne supporte pas les remboursements.');
    }

    private function baseUrl(): string
    {
        return $this->environment === 'production'
            ? 'https://proxy.momoapi.mtn.com'
            : 'https://sandbox.momodeveloper.mtn.com';
    }

    private function getAccessToken(): ?string
    {
        $response = Http::timeout(10)
            ->withBasicAuth($this->apiUser, $this->apiKey)
            ->withHeaders(['Ocp-Apim-Subscription-Key' => $this->subscriptionKey])
            ->post($this->baseUrl() . '/collection/token/');

        return $response->json('access_token');
    }
}
