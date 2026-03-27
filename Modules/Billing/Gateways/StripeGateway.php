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
 * Stripe payment gateway — International.
 * Uses Stripe Checkout Sessions for redirect-based flow.
 *
 * @see https://docs.stripe.com/api/checkout/sessions
 */
final class StripeGateway implements PaymentGatewayInterface
{
    private const BASE_URL = 'https://api.stripe.com/v1';

    private string $secretKey = '';
    private string $publishableKey = '';
    private string $webhookSecret = '';

    public function id(): string { return 'stripe'; }

    public function name(): string { return 'Stripe'; }

    public function configure(array $credentials): void
    {
        $this->secretKey = $credentials['secret_key'] ?? '';
        $this->publishableKey = $credentials['publishable_key'] ?? '';
        $this->webhookSecret = $credentials['webhook_secret'] ?? '';
    }

    public function isConfigured(): bool
    {
        return $this->secretKey !== '';
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Cle secrete Stripe manquante.'];
        }

        try {
            $response = Http::timeout(10)
                ->withBasicAuth($this->secretKey, '')
                ->get(self::BASE_URL . '/balance');

            return $response->successful()
                ? ['success' => true, 'message' => 'Connexion Stripe OK.']
                : ['success' => false, 'message' => 'Erreur Stripe : ' . ($response->json('error.message') ?? $response->status())];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function initiate(PaymentRequest $request): PaymentResponse
    {
        try {
            $response = Http::timeout(30)
                ->withBasicAuth($this->secretKey, '')
                ->asForm()
                ->post(self::BASE_URL . '/checkout/sessions', [
                    'payment_method_types[]' => 'card',
                    'mode' => 'payment',
                    'line_items[0][price_data][currency]' => strtolower($request->currency),
                    'line_items[0][price_data][product_data][name]' => $request->description,
                    'line_items[0][price_data][unit_amount]' => (int) ($request->amount * 100),
                    'line_items[0][quantity]' => 1,
                    'success_url' => $request->returnUrl . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => $request->cancelUrl ?? $request->returnUrl,
                    'client_reference_id' => $request->reference,
                    'customer_email' => $request->customerEmail,
                    'metadata[invoice_id]' => $request->invoiceId,
                    'metadata[reference]' => $request->reference,
                ]);

            $data = $response->json();

            if ($response->successful() && isset($data['id'])) {
                return new PaymentResponse(
                    success: true,
                    gatewayReference: $data['id'],
                    redirectUrl: $data['url'] ?? null,
                    status: 'pending',
                    metadata: ['session_id' => $data['id']],
                );
            }

            return new PaymentResponse(
                success: false,
                error: $data['error']['message'] ?? 'Erreur Stripe inconnue.',
                metadata: $data,
            );
        } catch (\Throwable $e) {
            return new PaymentResponse(success: false, error: $e->getMessage());
        }
    }

    public function verifyWebhook(array $payload, array $headers = []): WebhookResult
    {
        // Verify Stripe signature
        $signatureHeader = $headers['stripe-signature'] ?? $headers['Stripe-Signature'] ?? '';

        if ($this->webhookSecret !== '' && $signatureHeader !== '') {
            $rawBody = $payload['_raw_body'] ?? json_encode($payload);
            $elements = explode(',', $signatureHeader);
            $timestamp = '';
            $signatures = [];

            foreach ($elements as $el) {
                [$key, $value] = explode('=', $el, 2) + ['', ''];
                if ($key === 't') $timestamp = $value;
                if ($key === 'v1') $signatures[] = $value;
            }

            $signedPayload = "{$timestamp}.{$rawBody}";
            $expected = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

            $valid = false;
            foreach ($signatures as $sig) {
                if (hash_equals($expected, $sig)) {
                    $valid = true;
                    break;
                }
            }

            if (!$valid) {
                return new WebhookResult(valid: false, error: 'Invalid Stripe webhook signature.');
            }
        }

        $type = $payload['type'] ?? '';
        $object = $payload['data']['object'] ?? [];

        return new WebhookResult(
            valid: true,
            gatewayReference: $object['id'] ?? null,
            internalReference: $object['client_reference_id'] ?? $object['metadata']['reference'] ?? null,
            status: match ($type) {
                'checkout.session.completed' => 'completed',
                'checkout.session.expired' => 'cancelled',
                'payment_intent.payment_failed' => 'failed',
                default => 'pending',
            },
            rawPayload: $payload,
        );
    }

    public function checkStatus(string $gatewayReference): PaymentStatus
    {
        try {
            $response = Http::timeout(15)
                ->withBasicAuth($this->secretKey, '')
                ->get(self::BASE_URL . "/checkout/sessions/{$gatewayReference}");

            $data = $response->json();

            return new PaymentStatus(
                status: match ($data['payment_status'] ?? '') {
                    'paid' => 'completed',
                    'unpaid' => 'pending',
                    'no_payment_required' => 'completed',
                    default => 'unknown',
                },
                gatewayReference: $gatewayReference,
                amount: isset($data['amount_total']) ? $data['amount_total'] / 100 : null,
                currency: isset($data['currency']) ? strtoupper($data['currency']) : null,
                metadata: $data,
            );
        } catch (\Throwable) {
            return new PaymentStatus(status: 'unknown', gatewayReference: $gatewayReference);
        }
    }

    public function supportedCurrencies(): array
    {
        return ['EUR', 'USD', 'GBP', 'XOF', 'XAF', 'CAD', 'CHF', 'MAD'];
    }

    public function supportsRefunds(): bool { return true; }

    public function refund(string $gatewayReference, float $amount, string $currency): RefundResult
    {
        try {
            // First get the payment intent from the session
            $session = Http::timeout(15)
                ->withBasicAuth($this->secretKey, '')
                ->get(self::BASE_URL . "/checkout/sessions/{$gatewayReference}");

            $paymentIntent = $session->json('payment_intent');
            if (!$paymentIntent) {
                return new RefundResult(success: false, error: 'Payment intent introuvable.');
            }

            $response = Http::timeout(15)
                ->withBasicAuth($this->secretKey, '')
                ->asForm()
                ->post(self::BASE_URL . '/refunds', [
                    'payment_intent' => $paymentIntent,
                    'amount' => (int) ($amount * 100),
                ]);

            $data = $response->json();

            if ($response->successful() && isset($data['id'])) {
                return new RefundResult(
                    success: true,
                    refundReference: $data['id'],
                    amount: $data['amount'] / 100,
                );
            }

            return new RefundResult(success: false, error: $data['error']['message'] ?? 'Erreur remboursement Stripe.');
        } catch (\Throwable $e) {
            return new RefundResult(success: false, error: $e->getMessage());
        }
    }
}
