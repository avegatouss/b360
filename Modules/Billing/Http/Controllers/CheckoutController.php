<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Billing\Contracts\PaymentRequest;
use Modules\Billing\Services\GatewayManager;
use Modules\Billing\Services\InvoiceManager;
use Modules\Billing\Services\SubscriptionManager;
use Modules\Billing\Models\Payment;
use Modules\Core\Support\CurrentInstance;

/**
 * Checkout flow — subscribe to a plan and pay via selected gateway.
 */
final class CheckoutController extends Controller
{
    public function __construct(
        private readonly SubscriptionManager $subscriptionManager,
        private readonly InvoiceManager $invoiceManager,
        private readonly GatewayManager $gatewayManager,
    ) {}

    public function initiate(Request $request, string $slug)
    {
        $request->validate([
            'plan_id' => 'required|integer',
            'gateway' => 'required|string',
            'billing_period' => 'required|in:monthly,yearly',
        ]);

        $instance = CurrentInstance::get();
        $planId = $request->input('plan_id');
        $gatewaySlug = $request->input('gateway');
        $billingPeriod = $request->input('billing_period');

        // Create or get subscription
        $sub = $this->subscriptionManager->current($instance->id);

        if (!$sub) {
            $sub = $this->subscriptionManager->subscribe($instance->id, $planId, 0);
        } elseif ($sub->plan_id !== $planId) {
            $this->subscriptionManager->changePlan($sub, $planId);
            $sub->refresh();
        }

        // Generate invoice
        $invoice = $this->invoiceManager->generate($sub, $billingPeriod);

        // For manual gateway, just redirect to invoice
        if ($gatewaySlug === 'manual') {
            return redirect()->route('billing.invoices.show', [$instance->slug, $invoice->id])
                ->with('status', 'Facture generee. Veuillez effectuer le paiement.');
        }

        // Build payment request
        $reference = 'BIL-' . $instance->id . '-' . $invoice->id . '-' . substr(md5(uniqid()), 0, 8);

        $paymentRequest = new PaymentRequest(
            invoiceId: $invoice->id,
            amount: (float) $invoice->total,
            currency: $invoice->currency,
            description: "Abonnement B360 - " . $sub->plan->name,
            reference: $reference,
            callbackUrl: route('api.billing.webhooks.handle', ['gateway' => $gatewaySlug]),
            returnUrl: route('billing.invoices.show', [$instance->slug, $invoice->id]),
            cancelUrl: route('billing.index', $instance->slug),
            customerName: auth()->user()->name ?? null,
            customerEmail: auth()->user()->email ?? null,
            metadata: [
                'instance_id' => $instance->id,
                'invoice_id' => $invoice->id,
                'subscription_id' => $sub->id,
            ],
        );

        // Create pending payment record
        Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $invoice->total,
            'currency' => $invoice->currency,
            'method' => $gatewaySlug,
            'status' => 'pending',
            'reference' => $reference,
            'gateway_slug' => $gatewaySlug,
        ]);

        // Initiate payment
        $response = $this->gatewayManager->pay($gatewaySlug, $instance->id, $paymentRequest);

        if (!$response->success) {
            return back()->with('error', $response->error ?? 'Erreur lors de l\'initiation du paiement.');
        }

        // Handle response
        if ($response->redirectUrl) {
            return redirect()->away($response->redirectUrl);
        }

        if ($response->ussdCode) {
            return redirect()->route('billing.invoices.show', [$instance->slug, $invoice->id])
                ->with('ussd_code', $response->ussdCode)
                ->with('status', 'Composez le code USSD suivant pour finaliser le paiement.');
        }

        return redirect()->route('billing.invoices.show', [$instance->slug, $invoice->id])
            ->with('status', 'Paiement initie. Vous serez notifie une fois le paiement confirme.');
    }
}
