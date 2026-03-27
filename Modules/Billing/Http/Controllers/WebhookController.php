<?php

namespace Modules\Billing\Http\Controllers;

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

        // Log the webhook
        $log = WebhookLog::create([
            'gateway_slug' => $gateway,
            'payload' => $payload,
        ]);

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

        if (!$result->valid) {
            return response('Invalid webhook', 400);
        }

        // Find the payment by reference
        $payment = $this->findPayment($result->internalReference, $result->gatewayReference);

        if (!$payment) {
            $log->update(['result->error' => 'Payment not found']);
            return response('Payment not found', 200); // 200 to avoid retries
        }

        // Update instance_id on log
        $log->update(['instance_id' => $payment->invoice?->instance_id]);

        // Update payment status
        $this->updatePaymentStatus($payment, $result->status, $result->gatewayReference);

        return response('OK', 200);
    }

    private function findPayment(?string $internalRef, ?string $gatewayRef): ?Payment
    {
        if ($internalRef) {
            $payment = Payment::where('reference', $internalRef)->first();
            if ($payment) return $payment;
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
        if ($invoice && !$invoice->isPaid()) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        }
    }
}
