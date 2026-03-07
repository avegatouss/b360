<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Billing\Services\InvoiceManager;
use Modules\Billing\Services\SubscriptionManager;
use Modules\Core\Support\CurrentInstance;

final class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionManager $subscriptionManager,
        private readonly InvoiceManager $invoiceManager
    ) {}

    public function index(string $slug): View
    {
        $instance = CurrentInstance::get();
        $subscription = $this->subscriptionManager->current($instance->id);
        $invoices = $this->invoiceManager->forInstance($instance->id)->take(5);

        return view('billing::subscriptions.index', compact('instance', 'subscription', 'invoices'));
    }

    public function subscribe(Request $request, string $slug, string $plan): RedirectResponse
    {
        $instance = CurrentInstance::get();

        $this->subscriptionManager->subscribe($instance->id, (int) $plan);

        return redirect()->route('billing.index', $instance->slug)
            ->with('success', 'Abonnement active.');
    }

    public function update(Request $request, string $slug): RedirectResponse
    {
        $instance = CurrentInstance::get();
        $sub = $this->subscriptionManager->current($instance->id);
        abort_unless($sub, 404);

        $request->validate(['plan_id' => 'required|integer|exists:system.plans,id']);

        $this->subscriptionManager->changePlan($sub, $request->input('plan_id'));

        return redirect()->route('billing.index', $instance->slug)
            ->with('success', 'Plan modifie.');
    }

    public function cancel(Request $request, string $slug): RedirectResponse
    {
        $instance = CurrentInstance::get();
        $sub = $this->subscriptionManager->current($instance->id);
        abort_unless($sub, 404);

        $this->subscriptionManager->cancel($sub, $request->input('reason'));

        return redirect()->route('billing.index', $instance->slug)
            ->with('success', 'Abonnement annule.');
    }
}
