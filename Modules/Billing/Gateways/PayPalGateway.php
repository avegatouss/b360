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
 * PayPal payment gateway — International.
 * Uses PayPal REST API v2 (Orders API).
 *
 * @see https://developer.paypal.com/docs/api/orders/v2/
 */
final class PayPalGateway implements PaymentGatewayInterface
{
    private string $clientId = '';
    private string $clientSecret = '';
    private string $mode = 'sandbox'; // sandbox | live
    private string $webhookId = '';

    public function id(): string { return 'paypal'; }

    public function name(): string { return 'PayPal'; }

    public function configure(array $credentials): void
    {
        $this->clientId = $credentials['client_id'] ?? '';
        $this->clientSecret = $credentials['client_secret'] ?? '';
        $this->mode = $credentials['mode'] ?? 'sandbox';
        $this->webhookId = $credentials['webhook_id'] ?? '';
    }

    public function isConfigured(): bool
    {
        return $this->clientId !== '' && $this->clientSecret !== '';
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Identifiants PayPal manquants.'];
        }

        try {
            $token = $this->getAccessToken();

            return $token
                ? ['success' => true, 'message' => 'Connexion PayPal OK.']
                : ['success' => false, 'message' => 'Impossible d\'obtenir un token PayPal.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function initiate(PaymentRequest $request): PaymentResponse
    {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                return new PaymentResponse(success: false, error: 'Token PayPal non obtenu.');
            }

            $response = Http::timeout(30)
                ->withToken($token)
                ->post($this->baseUrl() . '/v2/checkout/orders', [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [[
                        'reference_id' => $request->reference,
                        'description' => $request->description,
                        'amount' => [
                            'currency_code' => $request->currency,
                            'value' => number_format($request->amount, 2, '.', ''),
                        ],
                        'custom_id' => (string) $request->invoiceId,
                    ]],
                    'payment_source' => [
                        'paypal' => [
                            'experience_context' => [
                                'return_url' => $request->returnUrl,
                                'cancel_url' => $request->cancelUrl ?? $request->returnUrl,
                                'brand_name' => 'B360',
                                'landing_page' => 'LOGIN',
                                'user_action' => 'PAY_NOW',
                            ],
                        ],
                    ],
                ]);

            $data = $response->json();

            if ($response->successful() && isset($data['id'])) {
                $approveLink = collect($data['links'] ?? [])
                    ->firstWhere('rel', 'payer-action');

                return new PaymentResponse(
                    success: true,
                    gatewayReference: $data['id'],
                    redirectUrl: $approveLink['href'] ?? null,
                    status: 'pending',
                    metadata: $data,
                );
            }

            $errorMsg = $data['details'][0]['description']
                ?? $data['message']
                ?? 'Erreur PayPal inconnue.';

            return new PaymentResponse(success: false, error: $errorMsg, metadata: $data ?? []);
        } catch (\Throwable $e) {
            return new PaymentResponse(success: false, error: $e->getMessage());
        }
    }

    public function verifyWebhook(array $payload, array $headers = []): WebhookResult
    {
        $eventType = $payload['event_type'] ?? '';
        $resource = $payload['resource'] ?? [];

        $referenceId = $resource['purchase_units'][0]['reference_id']
            ?? $resource['supplementary_data']['related_ids']['order_id']
            ?? null;

        return new WebhookResult(
            valid: $eventType !== '',
            gatewayReference: $resource['id'] ?? null,
            internalReference: $referenceId,
            status: match ($eventType) {
                'CHECKOUT.ORDER.APPROVED' => 'pending',
                'PAYMENT.CAPTURE.COMPLETED' => 'completed',
                'PAYMENT.CAPTURE.DENIED' => 'failed',
                'PAYMENT.CAPTURE.REFUNDED' => 'refunded',
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
                ->get($this->baseUrl() . "/v2/checkout/orders/{$gatewayReference}");

            $data = $response->json();

            return new PaymentStatus(
                status: match (strtoupper($data['status'] ?? '')) {
                    'COMPLETED' => 'completed',
                    'APPROVED' => 'pending',
                    'VOIDED' => 'cancelled',
                    default => 'pending',
                },
                gatewayReference: $gatewayReference,
                amount: isset($data['purchase_units'][0]['amount']['value'])
                    ? (float) $data['purchase_units'][0]['amount']['value'] : null,
                currency: $data['purchase_units'][0]['amount']['currency_code'] ?? null,
                metadata: $data,
            );
        } catch (\Throwable) {
            return new PaymentStatus(status: 'unknown', gatewayReference: $gatewayReference);
        }
    }

    public function supportedCurrencies(): array
    {
        return ['EUR', 'USD', 'GBP', 'CAD', 'CHF'];
    }

    public function supportsRefunds(): bool { return true; }

    public function refund(string $gatewayReference, float $amount, string $currency): RefundResult
    {
        try {
            $token = $this->getAccessToken();

            // Get capture ID from order
            $order = Http::timeout(15)->withToken($token)
                ->get($this->baseUrl() . "/v2/checkout/orders/{$gatewayReference}");

            $captureId = $order->json('purchase_units.0.payments.captures.0.id');
            if (!$captureId) {
                return new RefundResult(success: false, error: 'Capture ID introuvable.');
            }

            $response = Http::timeout(15)->withToken($token)
                ->post($this->baseUrl() . "/v2/payments/captures/{$captureId}/refund", [
                    'amount' => [
                        'value' => number_format($amount, 2, '.', ''),
                        'currency_code' => $currency,
                    ],
                ]);

            $data = $response->json();

            if ($response->successful() && isset($data['id'])) {
                return new RefundResult(success: true, refundReference: $data['id'], amount: $amount);
            }

            return new RefundResult(success: false, error: $data['message'] ?? 'Erreur remboursement PayPal.');
        } catch (\Throwable $e) {
            return new RefundResult(success: false, error: $e->getMessage());
        }
    }

    private function baseUrl(): string
    {
        return $this->mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    private function getAccessToken(): ?string
    {
        $response = Http::timeout(10)
            ->withBasicAuth($this->clientId, $this->clientSecret)
            ->asForm()
            ->post($this->baseUrl() . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        return $response->json('access_token');
    }
}
