<?php

namespace Modules\Eshop360\Http\Controllers\Invoice;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Invoice;
use Modules\Eshop360\Models\RecurringInvoice;
class RecurringInvoiceController extends Controller
{
    public function index(string $slug)
    {
        $instance = CurrentInstance::get();

        $recurringInvoices = RecurringInvoice::where('instance_id', $instance->id)
            ->with(['customer', 'templateInvoice'])
            ->latest()
            ->paginate(20);

        return view('eshop360::invoices.recurring.index', compact('recurringInvoices'));
    }

    public function create(string $slug)
    {
        $instance = CurrentInstance::get();
        $customers = Customer::where('instance_id', $instance->id)->where('is_active', true)
            ->orderBy('name')->get();
        $invoices = Invoice::where('instance_id', $instance->id)
            ->with('customer:id,name,code')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('eshop360::invoices.recurring.form', [
            'recurringInvoice' => null,
            'customers'        => $customers,
            'invoices'         => $invoices,
        ]);
    }

    public function store(Request $request, string $slug): RedirectResponse
    {
        $instance = CurrentInstance::get();

        $validated = $request->validate([
            'customer_id'         => 'required|exists:eshop_customers,id',
            'template_invoice_id' => 'required|exists:eshop_invoices,id',
            'frequency'           => 'required|in:weekly,biweekly,monthly,quarterly,yearly',
            'next_due_date'       => 'required|date|after_or_equal:today',
            'notes'               => 'nullable|string|max:2000',
        ]);

        RecurringInvoice::create([
            ...$validated,
            'instance_id' => $instance->id,
            'is_active'   => true,
        ]);

        return redirect()->route('eshop360.invoices.recurring.index', $instance->slug)
            ->with('success', 'Facture récurrente créée.');
    }

    public function edit(string $slug, RecurringInvoice $recurringInvoice)
    {
        $instance = CurrentInstance::get();
        $customers = Customer::where('instance_id', $instance->id)->where('is_active', true)
            ->orderBy('name')->get();
        $invoices = Invoice::where('instance_id', $instance->id)
            ->with('customer:id,name,code')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('eshop360::invoices.recurring.form', compact('recurringInvoice', 'customers', 'invoices'));
    }

    public function update(Request $request, string $slug, RecurringInvoice $recurringInvoice): RedirectResponse
    {
        $instance = CurrentInstance::get();

        $validated = $request->validate([
            'customer_id'         => 'required|exists:eshop_customers,id',
            'template_invoice_id' => 'required|exists:eshop_invoices,id',
            'frequency'           => 'required|in:weekly,biweekly,monthly,quarterly,yearly',
            'next_due_date'       => 'required|date',
            'notes'               => 'nullable|string|max:2000',
        ]);

        $recurringInvoice->update($validated);

        return redirect()->route('eshop360.invoices.recurring.index', $instance->slug)
            ->with('success', 'Facture récurrente mise à jour.');
    }

    public function destroy(string $slug, RecurringInvoice $recurringInvoice): RedirectResponse
    {
        $instance = CurrentInstance::get();
        $recurringInvoice->delete();

        return redirect()->route('eshop360.invoices.recurring.index', $instance->slug)
            ->with('success', 'Facture récurrente supprimée.');
    }

    public function toggle(string $slug, RecurringInvoice $recurringInvoice): RedirectResponse
    {
        $instance = CurrentInstance::get();
        $recurringInvoice->update(['is_active' => !$recurringInvoice->is_active]);

        $status = $recurringInvoice->is_active ? 'activée' : 'désactivée';

        return redirect()->route('eshop360.invoices.recurring.index', $instance->slug)
            ->with('success', "Facture récurrente {$status}.");
    }
}
