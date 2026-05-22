<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Services\InvoiceManager;
use Modules\Core\Support\CurrentInstance;

final class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceManager $invoiceManager
    ) {}

    public function index(string $slug): View
    {
        $instance = CurrentInstance::get();
        $invoices = $this->invoiceManager->forInstance($instance->id);

        return view('billing::invoices.index', compact('instance', 'invoices'));
    }

    public function show(string $slug, string $invoice): View
    {
        $instance = CurrentInstance::get();
        $invoice = Invoice::findOrFail((int) $invoice);

        return view('billing::invoices.show', compact('instance', 'invoice'));
    }

    public function pay(Request $request, string $slug, string $invoice): RedirectResponse
    {
        $invoice = Invoice::findOrFail((int) $invoice);

        $request->validate([
            'method' => 'required|string|in:manual,bank_transfer,card,stripe,paypal',
            'reference' => 'nullable|string|max:255',
        ]);

        $this->invoiceManager->markPaid(
            $invoice,
            $request->input('method'),
            $request->input('reference')
        );

        $instance = CurrentInstance::get();

        return redirect()->route('billing.invoices.index', $instance->slug)
            ->with('success', 'Paiement enregistre.');
    }
}
