<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Billing\Models\Payment;
use Modules\Billing\Models\WebhookLog;
use Modules\Billing\Services\GatewayManager;
use Modules\Billing\Services\InvoiceManager;

/**
 * Centralized webhook handler for all payment gateways.
 * Route: POST /api/billing/webhooks/{gateway}
 *
 * R-002 — Idempotence webhook :
 *   Chaque webhook reçu est dédupliqué via une clé idempotency
 *   (UNIQUE sur billing_webhook_logs.idempotency_key). Un retry de la
 *   passerelle (Stripe/CinetPay/etc.) est détecté et ne re-déclenche
 *   pas `updatePaymentStatus()`. Voir ADR-003.
 */
final class WebhookController extends Controller
{
    public function __construct(
        private readonly GatewayManager $gatewayManager,
        private readonly InvoiceManager $invoiceManager,
    ) {}

    public function handle(Request $request, string $gateway): Response
    {
        $payload = $request->all();
        $headers = $request->headers->all();
        // Flatten header arrays
        $flatHeaders = array_map(fn ($v) => is_array($v) ? ($v[0] ?? '') : $v, $headers);

        // Store raw body for signature verification
        $payload['_raw_body'] = $request->getContent();

        $idempotencyKey = $this->buildIdempotencyKey($gateway, $payload, $flatHeaders);

        // Idempotence : premier enregistrement gagne. Un retry voit la
        // contrainte UNIQUE échouer et sort immédiatement en 200 OK sans
        // re-traiter le paiement. Les 2ᵉ + visibles dans les logs comme
        // le record initial — pas de pollution.
        try {
            $log = WebhookLog::create([
                'gateway_slug' => $gateway,
                'idempotency_key' => $idempotencyKey,
                'payload' => $payload,
            ]);
        } catch (UniqueConstraintViolationException) {
            return response('OK (replay)', 200);
        }

        // Verify webhook
        $result = $this->gatewayManager->handleWebhook($gateway, $payload, $flatHeaders);

        // Update log
        $log->update([
            'event_type' => $result->status,
            'result' => [
                'valid' => $result->valid,
                'status' => $result->status,
                'gateway_reference' => $result->gatewayReference,
                'internal_reference' => $result->internalReference,
                'error' => $result->error,
            ],
            'processed_at' => now(),
        ]);

        if (! $result->valid) {
            return response('Invalid webhook', 400);
        }

        // Find the payment by reference
        $payment = $this->findPayment($result->internalReference, $result->gatewayReference);

        if (! $payment) {
            $log->update(['result->error' => 'Payment not found']);

            return response('Payment not found', 200); // 200 to avoid retries
        }

        // Update instance_id on log
        $log->update(['instance_id' => $payment->invoice?->instance_id]);

        // Update payment status
        $this->updatePaymentStatus($payment, $result->status, $result->gatewayReference);

        return response('OK', 200);
    }

    /**
     * Build a stable idempotency key for the incoming webhook.
     *
     * Preferred source: explicit event id (Stripe `id`, CinetPay `cpm_trans_id`,
     * generic `event_id` / `transaction_id`), combined with the gateway slug
     * so the same id from two different gateways never collides.
     *
     * Fallback: SHA-256 of the raw body — covers edge cases where a gateway
     * does not expose a stable event id (manual gateway, legacy integrations).
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $headers
     */
    private function buildIdempotencyKey(string $gateway, array $payload, array $headers): string
    {
        $candidates = [
            $payload['id'] ?? null,
            $payload['event_id'] ?? null,
            $payload['transaction_id'] ?? null,
            $payload['cpm_trans_id'] ?? null,
            $headers['stripe-signature'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return substr("{$gateway}:{$candidate}", 0, 128);
            }
        }

        $rawBody = $payload['_raw_body'] ?? '';

        return substr("{$gateway}:body:".hash('sha256', (string) $rawBody), 0, 128);
    }

    private function findPayment(?string $internalRef, ?string $gatewayRef): ?Payment
    {
        if ($internalRef) {
            $payment = Payment::where('reference', $internalRef)->first();
            if ($payment) {
                return $payment;
            }
        }

        if ($gatewayRef) {
            return Payment::where('gateway_reference', $gatewayRef)->first();
        }

        return null;
    }

    private function updatePaymentStatus(Payment $payment, string $status, ?string $gatewayRef): void
    {
        if ($gatewayRef) {
            $payment->gateway_reference = $gatewayRef;
        }

        match ($status) {
            'completed' => $this->markCompleted($payment),
            'failed' => $payment->update(['status' => 'failed']),
            'cancelled' => $payment->update(['status' => 'failed']),
            default => null,
        };
    }

    private function markCompleted(Payment $payment): void
    {
        $payment->update([
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        // Mark the invoice as paid
        $invoice = $payment->invoice;
        if ($invoice && ! $invoice->isPaid()) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        }
    }
}
