<?php

namespace Modules\Eshop360\Http\Controllers\Invoice;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Services\EshopSettingsService;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Invoice;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Services\EmailService;
use Modules\Eshop360\Services\InvoiceService;
use Modules\Eshop360\Services\PdfService;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly EshopSettingsService $eshopSettings,
    ) {
    }

    public function index(Request $request)
    {
        $instance = CurrentInstance::get();

        $query = Invoice::with(['customer'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->customer_id, fn ($q, $c) => $q->where('customer_id', $c))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('eshop_invoices.created_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('eshop_invoices.created_at', '<=', $d))
            ->when($request->overdue, fn ($q) => $q->where(function ($sq) {
                $sq->where('status', 'overdue')
                    ->orWhere(fn ($sq2) => $sq2->where('status', '!=', 'paid')->whereNotNull('due_date')->where('due_date', '<', now()));
            }))
            ->when($request->search, fn ($q, $s) => $q->where(function ($qq) use ($s) {
                $qq->where('invoice_number', 'like', "%{$s}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$s}%"));
            }));

        // KPIs
        $fq = clone $query;
        $kpi = (object) [
            'total'      => (clone $fq)->count(),
            'amount'     => round((float) (clone $fq)->sum('total'), 0),
            'paid'       => round((float) (clone $fq)->where('status', 'paid')->sum('total'), 0),
            'due'        => round((float) (clone $fq)->whereIn('status', ['unpaid', 'partial', 'overdue'])->sum('due_amount'), 0),
            'overdue'    => (clone $fq)->where(function ($sq) {
                $sq->where('status', 'overdue')
                    ->orWhere(fn ($sq2) => $sq2->where('status', '!=', 'paid')->whereNotNull('due_date')->where('due_date', '<', now()));
            })->count(),
            'draft'      => (clone $fq)->where('status', 'draft')->count(),
            'paid_count' => (clone $fq)->where('status', 'paid')->count(),
        ];

        $invoices = $query->latest()->paginate(25)->withQueryString();

        // Lookups
        $customers = Customer::where('instance_id', $instance->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('eshop360::invoices.index', compact('invoices', 'kpi', 'customers'));
    }

    public function create(Request $request)
    {
        $instance = CurrentInstance::get();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::active()->orderBy('name')->get();
        $order = $request->order_id ? Order::with('items.product', 'customer')->find($request->order_id) : null;

        $instanceId = $instance?->id ?? 0;
        $settings = $this->eshopSettings->get('invoice');

        return view('eshop360::invoices.create', compact('customers', 'products', 'order', 'settings'));
    }

    public function store(Request $request): RedirectResponse
    {
        $instance = CurrentInstance::get();
        $validated = $request->validate([
            'customer_id'            => 'nullable|exists:eshop_customers,id',
            'order_id'               => 'nullable|exists:eshop_orders,id',
            'due_date'               => 'nullable|date|after_or_equal:today',
            'notes'                  => 'nullable|string|max:2000',
            'terms'                  => 'nullable|string|max:2000',
            'footer_text'            => 'nullable|string|max:1000',
            'template'               => 'nullable|string|max:50',
            'discount_amount'        => 'nullable|numeric|min:0',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'nullable|exists:eshop_products,id',
            'items.*.description'    => 'required|string|max:500',
            'items.*.quantity'       => 'required|integer|min:1',
            'items.*.unit_price'     => 'required|numeric|min:0',
            'items.*.discount'       => 'nullable|numeric|min:0',
            'items.*.tax'            => 'nullable|numeric|min:0',
        ]);

        $invoice = $this->invoiceService->createFromItems($validated['items'], [
            'instance_id' => $instance?->id,
            'order_id' => $validated['order_id'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
            'status' => 'draft',
            'due_date' => $validated['due_date'] ?? null,
            'discount_amount' => (float) ($validated['discount_amount'] ?? 0),
            'notes' => $validated['notes'] ?? null,
            'terms' => $validated['terms'] ?? null,
            'footer_text' => $validated['footer_text'] ?? null,
            'template' => $validated['template'] ?? 'default',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('eshop360.invoices.show', [
            'slug' => $instance?->slug,
            'invoice' => $invoice,
        ])
            ->with('success', __('Invoice :number created.', ['number' => $invoice->invoice_number]));
    }

    public function show(string $slug, Invoice $invoice)
    {
        $invoice->load(['customer', 'order', 'items.product', 'payments']);

        return view('eshop360::invoices.show', compact('invoice'));
    }

    public function update(Request $request, string $slug, Invoice $invoice): RedirectResponse
    {
        $instance = CurrentInstance::get();
        $validated = $request->validate([
            'status'      => 'nullable|in:draft,sent,paid,unpaid,overdue,cancelled',
            'due_date'    => 'nullable|date',
            'paid_amount' => 'nullable|numeric|min:0',
            'notes'       => 'nullable|string|max:2000',
            'terms'       => 'nullable|string|max:2000',
            'footer_text' => 'nullable|string|max:1000',
            'template'    => 'nullable|string|max:50',
        ]);

        $updateData = array_filter($validated, fn ($v) => $v !== null);

        if (isset($updateData['paid_amount'])) {
            $method = $invoice->order?->payment_method ?? 'cash';
            $this->invoiceService->syncPaidAmount(
                $invoice,
                (float) $updateData['paid_amount'],
                $method,
                'INV-UPD',
                'Manual invoice payment update'
            );
            unset($updateData['paid_amount'], $updateData['status']);
        }

        $invoice->update($updateData);

        return redirect()->route('eshop360.invoices.show', [
            'slug' => $instance?->slug,
            'invoice' => $invoice,
        ])
            ->with('success', __('Invoice updated successfully.'));
    }

    public function recordPayment(Request $request, string $slug, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'amount'  => 'required|numeric|min:1',
            'method'  => 'required|string|in:cash,card,cheque,paypal,bank_transfer,points,deposit,gift_card,external,manual',
            'notes'   => 'nullable|string|max:500',
        ]);

        $amount = (float) $validated['amount'];
        $maxPayable = (float) $invoice->due_amount;

        if ($amount > $maxPayable && $maxPayable > 0) {
            $amount = $maxPayable;
        }

        $newPaid = (float) $invoice->paid_amount + $amount;

        $this->invoiceService->syncPaidAmount(
            $invoice,
            $newPaid,
            $validated['method'],
            'INV-PAY',
            $validated['notes'] ?? __('Paiement facture')
        );

        // Also update the linked order if exists
        if ($invoice->order_id) {
            $order = $invoice->order;
            if ($order) {
                $order->update([
                    'paid_amount' => $newPaid,
                    'due_amount' => max(0, (float) $order->total - $newPaid),
                    'payment_status' => $newPaid >= (float) $order->total ? 'paid' : ($newPaid > 0 ? 'partial' : 'unpaid'),
                ]);
            }
        }

        return redirect()->route('eshop360.invoices.show', [$slug, $invoice])
            ->with('success', __('Paiement de :amount enregistre.', ['amount' => number_format($amount, 0, ',', ' ')]));
    }

    public function destroy(string $slug, Invoice $invoice): RedirectResponse
    {
        $instance = CurrentInstance::get();
        $invoice->delete();

        return redirect()->route('eshop360.invoices.index', ['slug' => $instance?->slug])
            ->with('success', __('Invoice deleted successfully.'));
    }

    public function templates()
    {
        $invoiceTemplates = [
            ['name' => 'default', 'label' => __('Standard'), 'description' => __('Template facture classique'), 'icon' => 'ti-file-invoice', 'color' => 'primary'],
            ['name' => 'invoice-a4-v1', 'label' => __('A4 Version 1'), 'description' => __('Format A4 professionnel'), 'icon' => 'ti-file-text', 'color' => 'info'],
            ['name' => 'invoice-a4-v2', 'label' => __('A4 Version 2'), 'description' => __('Format A4 moderne'), 'icon' => 'ti-file-text', 'color' => 'success'],
            ['name' => 'invoice-a4-compact', 'label' => __('A4 Compact'), 'description' => __('Format condense sur une page'), 'icon' => 'ti-layout-rows', 'color' => 'warning'],
            ['name' => 'invoice-gst-v1', 'label' => __('GST Version 1'), 'description' => __('Conforme aux normes fiscales GST'), 'icon' => 'ti-receipt-tax', 'color' => 'danger'],
            ['name' => 'invoice-gst-v2', 'label' => __('GST Version 2'), 'description' => __('GST avec detail des taxes'), 'icon' => 'ti-receipt-tax', 'color' => 'secondary'],
        ];

        $otherTemplates = [
            ['name' => 'quotation', 'label' => __('Devis / Proforma'), 'description' => __('Template pour les devis clients'), 'icon' => 'ti-clipboard-list', 'color' => 'info'],
            ['name' => 'proforma', 'label' => __('Facture proforma'), 'description' => __('Facture provisoire avant paiement'), 'icon' => 'ti-file-dots', 'color' => 'primary'],
            ['name' => 'delivery-note', 'label' => __('Bon de livraison'), 'description' => __('Document d\'accompagnement livraison'), 'icon' => 'ti-truck', 'color' => 'success'],
            ['name' => 'credit-note', 'label' => __('Avoir / Note de credit'), 'description' => __('Document de remboursement'), 'icon' => 'ti-receipt-refund', 'color' => 'danger'],
            ['name' => 'order-receipt', 'label' => __('Recu de commande'), 'description' => __('Ticket de caisse POS'), 'icon' => 'ti-receipt', 'color' => 'warning'],
        ];

        $currentTemplate = $this->eshopSettings->value('invoice', 'default_template', 'default');

        $settings = $this->eshopSettings->get('invoice');

        return view('eshop360::invoices.templates', compact('invoiceTemplates', 'otherTemplates', 'currentTemplate', 'settings'));
    }

    public function settings()
    {
        $instanceId = CurrentInstance::get()?->id ?? 0;
        $settings = $this->eshopSettings->get('invoice');

        return view('eshop360::invoices.settings', compact('settings'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name'    => 'required|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_phone'   => 'nullable|string|max:30',
            'company_email'   => 'nullable|email|max:255',
            'company_logo'    => 'nullable|image|max:1024',
            'tax_number'      => 'nullable|string|max:100',
            'default_terms'   => 'nullable|string|max:2000',
            'default_footer'  => 'nullable|string|max:1000',
            'default_due_days' => 'nullable|integer|min:0|max:365',
            'default_template' => 'nullable|string|max:50',
            'currency_symbol'  => 'nullable|string|max:10',
            'currency_position' => 'nullable|in:before,after',
        ]);

        if ($request->hasFile('company_logo')) {
            $validated['company_logo'] = $request->file('company_logo')->store('invoice_settings', 'public');
        }

        $instanceId = CurrentInstance::get()?->id ?? 0;
        $this->eshopSettings->set('invoice', $validated);

        return redirect()->route('eshop360.invoices.settings')
            ->with('success', __('Invoice settings updated successfully.'));
    }

    public function report(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        $invoicesByStatus = Invoice::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('status')
            ->get();

        $monthlyInvoices = Invoice::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total) as total'),
                DB::raw('SUM(paid_amount) as paid'),
                DB::raw('SUM(due_amount) as due'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $totalInvoiced = Invoice::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->sum('total');
        $totalPaid = Invoice::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->sum('paid_amount');
        $totalDue = Invoice::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->sum('due_amount');
        $overdueCount = Invoice::where('status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->count();

        return view('eshop360::invoices.report', compact(
            'invoicesByStatus', 'monthlyInvoices',
            'totalInvoiced', 'totalPaid', 'totalDue', 'overdueCount',
            'dateFrom', 'dateTo'
        ));
    }

    public function pdf(string $slug, Invoice $invoice)
    {
        $invoice->load('items.product', 'customer', 'order');
        $html = app(\Modules\Eshop360\Services\PdfService::class)->invoice($invoice);
        return app(\Modules\Eshop360\Services\PdfService::class)->download($html, "facture-{$invoice->invoice_number}.pdf");
    }

    public function sendEmail(string $slug, Invoice $invoice): RedirectResponse
    {
        $sent = app(\Modules\Eshop360\Services\EmailService::class)->sendInvoice($invoice);

        if (!$sent) {
            return redirect()->back()->with('error', 'Impossible d\'envoyer l\'email (client sans email ou erreur SMTP).');
        }

        return redirect()->back()->with('success', "Facture envoyée par email.");
    }

}
