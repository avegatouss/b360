<?php

namespace Modules\Eshop360\Http\Controllers\Payment;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Eshop360\Domain\Finance\Models\EshopPaymentGateway;
use Modules\Eshop360\Domain\Finance\Models\Invoice;
use Modules\Eshop360\Domain\Finance\Models\Payment;
use Modules\Eshop360\Services\Payment\PaymentGatewayManager;

class PublicPaymentController extends Controller
{
    public function __construct(
        private readonly PaymentGatewayManager $manager,
    ) {}

    /**
     * Show public payment page for an invoice.
     */
    public function show(string $token)
    {
        $invoice = Invoice::where('payment_token', $token)
            ->with(['customer', 'items'])
            ->firstOrFail();

        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return view('eshop360::payment.success', [
                'invoice' => $invoice,
                'message' => $invoice->status === 'paid' ? 'Cette facture est deja payee.' : 'Cette facture est annulee.',
            ]);
        }

        $gateways = EshopPaymentGateway::where('instance_id', $invoice->instance_id)
            ->active()->get();

        return view('eshop360::payment.public', compact('invoice', 'gateways', 'token'));
    }

    /**
     * Initiate payment with selected gateway.
     */
    public function initiate(Request $request, string $token)
    {
        $request->validate([
            'gateway_id' => 'required|integer',
        ]);

        $invoice = Invoice::where('payment_token', $token)
            ->with(['customer'])
            ->firstOrFail();

        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return redirect()->back()->with('error', 'Cette facture ne peut pas etre payee.');
        }

        $gateway = EshopPaymentGateway::where('instance_id', $invoice->instance_id)
            ->where('is_active', true)
            ->findOrFail($request->input('gateway_id'));

        $dueAmount = (float) $invoice->due_amount > 0 ? (float) $invoice->due_amount : (float) $invoice->total;

        $meta = [
            'reference' => $invoice->reference.'-'.strtoupper(substr(md5(uniqid('', true)), 0, 6)),
            'description' => 'Facture '.$invoice->invoice_number,
            'customer_name' => $invoice->customer?->name ?? '',
            'customer_email' => $invoice->customer?->email ?? '',
            'customer_phone' => $invoice->customer?->phone ?? '',
            'customer_id' => $invoice->customer_id,
            'invoice_id' => $invoice->id,
            'return_url' => route('eshop360.payment.callback', ['gateway' => $gateway->driver, 'token' => $token]),
            'cancel_url' => route('eshop360.payment.show', $token),
            'webhook_url' => route('eshop360.payment.webhook', $gateway->driver),
        ];

        try {
            $driver = $this->manager->driver($gateway->driver, $gateway->getDecryptedConfig());
            $result = $driver->initiate($dueAmount, 'XOF', $meta);

            if (! $result['success']) {
                return redirect()->back()->with('error', $result['error'] ?? 'Erreur lors de l\'initiation du paiement.');
            }

            // Record pending payment
            $invoice->payments()->create([
                'instance_id' => $invoice->instance_id,
                'amount' => $dueAmount,
                'method' => 'external',
                'gateway' => $gateway->driver,
                'reference' => $meta['reference'],
                'gateway_reference' => $result['transaction_id'] ?? null,
                'status' => 'pending',
                'notes' => "Paiement via {$gateway->display_name}",
                'metadata' => [
                    'initiation' => $result,
                    'gateway_id' => $gateway->id,
                    'instance_id' => $invoice->instance_id,
                    'invoice_id' => $invoice->id,
                    'payment_token' => $invoice->payment_token,
                ],
            ]);

            // Redirect to gateway if URL provided
            if (! empty($result['redirect_url'])) {
                return redirect()->away($result['redirect_url']);
            }

            // For drivers without redirect (wallet, etc.), check immediate success
            if ($result['success']) {
                return redirect()->route('eshop360.payment.success', $token)
                    ->with('success', 'Paiement effectue avec succes.');
            }

            return redirect()->back()->with('info', 'Paiement initie. Transaction: '.($result['transaction_id'] ?? 'N/A'));
        } catch (\Throwable $e) {
            Log::error('Payment initiation error', ['error' => $e->getMessage(), 'invoice' => $invoice->id]);

            return redirect()->back()->with('error', 'Erreur technique. Veuillez reessayer.');
        }
    }

    /**
     * Handle return from payment gateway.
     */
    public function callback(string $gateway, Request $request)
    {
        $token = $request->query('token', $request->input('token', ''));

        $invoice = Invoice::where('payment_token', $token)->first();

        if (! $invoice) {
            return view('eshop360::payment.failed', ['message' => 'Facture introuvable.']);
        }

        // Try to verify the payment
        $gatewayConfig = EshopPaymentGateway::where('instance_id', $invoice->instance_id)
            ->where('driver', $gateway)->where('is_active', true)->first();

        if ($gatewayConfig) {
            $payment = $this->resolveInvoicePayment($invoice, $gatewayConfig);

            if ($payment && $payment->gateway_reference) {
                try {
                    $driver = $this->manager->driver($gateway, $gatewayConfig->getDecryptedConfig());
                    $result = $driver->verify($payment->gateway_reference);

                    $newStatus = match ($result['status'] ?? 'unknown') {
                        'completed' => 'completed',
                        'failed', 'cancelled' => 'failed',
                        default => 'pending',
                    };

                    $payment->update([
                        'status' => $newStatus,
                        'metadata' => array_merge($payment->metadata ?? [], ['verification' => $result]),
                    ]);

                    if ($newStatus === 'completed') {
                        $this->syncInvoiceTotals($invoice);

                        return view('eshop360::payment.success', ['invoice' => $invoice, 'message' => 'Paiement confirme.']);
                    }
                } catch (\Throwable $e) {
                    Log::error('Payment verification failed', ['error' => $e->getMessage()]);
                }
            }
        }

        // If verification pending or inconclusive
        return view('eshop360::payment.success', [
            'invoice' => $invoice,
            'message' => 'Votre paiement est en cours de traitement.',
        ]);
    }

    /**
     * Handle webhook from payment gateway.
     */
    public function webhook(string $gateway, Request $request)
    {
        Log::info("Payment webhook received for {$gateway}", ['payload' => $request->all()]);

        // Find all active instances with this gateway
        $gatewayConfigs = EshopPaymentGateway::where('driver', $gateway)
            ->where('is_active', true)->get();

        foreach ($gatewayConfigs as $config) {
            try {
                $driver = $this->manager->driver($gateway, $config->getDecryptedConfig());
                $result = $driver->handleWebhook($request);

                if (! $result['valid']) {
                    continue;
                }

                $transactionId = $result['transaction_id'] ?? null;
                $reference = $result['reference'] ?? null;

                if (! $transactionId && ! $reference) {
                    continue;
                }

                $payment = $this->resolveWebhookPayment(
                    $config,
                    $gateway,
                    $transactionId,
                    $reference
                );

                if (! $payment) {
                    continue;
                }

                $newStatus = match ($result['status'] ?? 'pending') {
                    'completed' => 'completed',
                    'failed', 'cancelled' => 'failed',
                    default => 'pending',
                };

                $payment->update([
                    'status' => $newStatus,
                    'metadata' => array_merge($payment->metadata ?? [], ['webhook' => $result]),
                ]);

                if ($newStatus === 'completed' && $payment->payable instanceof Invoice) {
                    $this->syncInvoiceTotals($payment->payable);
                }

                return response()->json(['status' => 'ok']);
            } catch (\Throwable $e) {
                Log::error("Webhook processing error for {$gateway}", ['error' => $e->getMessage()]);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Payment success page.
     */
    public function success(string $token)
    {
        $invoice = Invoice::where('payment_token', $token)
            ->with('payments')
            ->first();

        $message = 'Paiement effectue avec succes.';

        if (! $invoice) {
            return view('eshop360::payment.failed', [
                'message' => 'Facture introuvable.',
                'token' => $token,
            ]);
        }

        if ($invoice->status === 'paid') {
            $message = 'Paiement confirme.';
        } elseif ($invoice->payments->contains(fn (Payment $payment) => $payment->status === 'pending')) {
            $message = 'Votre paiement est en cours de traitement.';
        } elseif ($invoice->payments->contains(fn (Payment $payment) => $payment->status === 'failed')) {
            $message = 'Le paiement a echoue ou a ete annule.';
        }

        return view('eshop360::payment.success', [
            'invoice' => $invoice,
            'message' => $message,
        ]);
    }

    /**
     * Sync invoice paid/due amounts from payments.
     */
    private function syncInvoiceTotals(Invoice $invoice): void
    {
        $paidAmount = (float) $invoice->payments()
            ->where('status', 'completed')
            ->sum('amount');

        $dueAmount = max(0, (float) $invoice->total - $paidAmount);
        $status = 'unpaid';

        if ($paidAmount >= (float) $invoice->total) {
            $status = 'paid';
        } elseif ($paidAmount > 0) {
            $status = 'partial';
        }

        $invoice->update([
            'paid_amount' => round($paidAmount, 2),
            'due_amount' => round($dueAmount, 2),
            'status' => $status,
        ]);
    }

    private function resolveInvoicePayment(Invoice $invoice, EshopPaymentGateway $gatewayConfig): ?Payment
    {
        return $invoice->payments()
            ->where('gateway', $gatewayConfig->driver)
            ->where('status', 'pending')
            ->get()
            ->filter(function (Payment $payment) use ($gatewayConfig): bool {
                $metadata = is_array($payment->metadata) ? $payment->metadata : [];

                return (int) ($metadata['gateway_id'] ?? 0) === (int) $gatewayConfig->id;
            })
            ->sortByDesc('id')
            ->first();
    }

    private function resolveWebhookPayment(
        EshopPaymentGateway $config,
        string $gateway,
        ?string $transactionId,
        ?string $reference
    ): ?Payment {
        $payments = Payment::query()
            ->where('instance_id', $config->instance_id)
            ->where('gateway', $gateway)
            ->where('status', 'pending')
            ->when($transactionId, fn ($query) => $query->where('gateway_reference', $transactionId))
            ->latest()
            ->get();

        return $payments->first(function (Payment $payment) use ($config, $transactionId, $reference): bool {
            $metadata = is_array($payment->metadata) ? $payment->metadata : [];
            $gatewayId = (int) ($metadata['gateway_id'] ?? 0);

            if ($gatewayId !== 0 && $gatewayId !== (int) $config->id) {
                return false;
            }

            if ($transactionId !== null && $payment->gateway_reference !== $transactionId) {
                return false;
            }

            if ($reference !== null && $payment->reference !== $reference) {
                return false;
            }

            return true;
        });
    }
}
