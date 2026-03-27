<?php

namespace Modules\Eshop360\Http\Controllers\Promotion;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Invoice;
use Modules\Eshop360\Models\InvoiceItem;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Quotation;
use Modules\Eshop360\Services\PdfService;

class QuotationController extends Controller
{

    public function index(Request $request)
    {
        $instanceId = CurrentInstance::idOrFail();

        $query = Quotation::where('instance_id', $instanceId)->with(['customer', 'items.product'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->where(function ($qq) use ($s) {
                $qq->where('reference', 'like', "%{$s}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$s}%"));
            }))
            ->when($request->customer_id, fn ($q, $c) => $q->where('customer_id', $c))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest();

        $quotations = $query->paginate(20)->withQueryString();

        $customers = Customer::where('instance_id', $instanceId)
            ->orderBy('name')->get(['id', 'name']);

        $products = Product::where('instance_id', $instanceId)->where('is_active', true)
            ->orderBy('name')->get(['id', 'name', 'sku', 'price', 'tax_rate']);

        return view('eshop360::promotions.quotations', compact('quotations', 'customers', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id'            => 'nullable|exists:eshop_customers,id',
            'valid_until'            => 'nullable|date|after_or_equal:today',
            'notes'                  => 'nullable|string|max:2000',
            'terms'                  => 'nullable|string|max:2000',
            'discount_amount'        => 'nullable|numeric|min:0',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:eshop_products,id',
            'items.*.quantity'       => 'required|integer|min:1',
            'items.*.unit_price'     => 'required|numeric|min:0',
            'items.*.discount'       => 'nullable|numeric|min:0',
        ]);

        $quotation = DB::transaction(function () use ($validated) {
            $subtotal = 0;
            $taxAmount = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $itemDiscount = $item['discount'] ?? 0;
                $itemTax = round(($item['unit_price'] * $item['quantity'] - $itemDiscount) * ($product->tax_rate / 100), 2);
                $itemTotal = ($item['unit_price'] * $item['quantity']) - $itemDiscount + $itemTax;

                $subtotal += $item['unit_price'] * $item['quantity'];
                $taxAmount += $itemTax;

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount'   => $itemDiscount,
                    'tax'        => $itemTax,
                    'total'      => $itemTotal,
                ];
            }

            $discountAmount = $validated['discount_amount'] ?? 0;
            $total = $subtotal + $taxAmount - $discountAmount;

            $quotation = Quotation::create([
                'instance_id'     => CurrentInstance::idOrFail(),
                'customer_id'     => $validated['customer_id'] ?? null,
                'reference'       => 'QUO-' . now()->format('Ymd') . '-' . str_pad(Quotation::count() + 1, 4, '0', STR_PAD_LEFT),
                'status'          => 'draft',
                'valid_until'     => $validated['valid_until'] ?? now()->addDays(30),
                'subtotal'        => $subtotal,
                'tax_amount'      => $taxAmount,
                'discount_amount' => $discountAmount,
                'total'           => $total,
                'notes'           => $validated['notes'] ?? null,
                'terms'           => $validated['terms'] ?? null,
                'created_by'      => auth()->id(),
            ]);

            foreach ($itemsData as $itemData) {
                $quotation->items()->create($itemData);
            }

            return $quotation;
        });

        return redirect()->route('eshop360.quotations.show', ['quotation' => $quotation])
            ->with('success', __('Quotation :ref created.', ['ref' => $quotation->reference]));
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['customer', 'items.product']);

        return view('eshop360::promotions.quotation-show', compact('quotation'));
    }

    public function update(Request $request, Quotation $quotation): RedirectResponse
    {
        $validated = $request->validate([
            'status'      => 'nullable|in:draft,sent,accepted,rejected,expired,converted',
            'valid_until' => 'nullable|date',
            'notes'       => 'nullable|string|max:2000',
            'terms'       => 'nullable|string|max:2000',
        ]);

        $quotation->update(array_filter($validated, fn ($v) => $v !== null));

        return redirect()->route('eshop360.quotations.show', ['quotation' => $quotation])
            ->with('success', __('Quotation updated successfully.'));
    }

    public function destroy(Quotation $quotation): RedirectResponse
    {
        $quotation->items()->delete();
        $quotation->delete();

        return redirect()->route('eshop360.quotations.index')
            ->with('success', __('Quotation deleted successfully.'));
    }

    /**
     * Convert a quotation to an invoice.
     */
    public function convertToInvoice(Quotation $quotation): RedirectResponse
    {
        if ($quotation->status === 'converted') {
            return redirect()->back()->with('error', 'Ce devis a déjà été converti en facture.');
        }

        $invoice = DB::transaction(function () use ($quotation) {
            $instance = $quotation->instance_id ?? session('instance_id');

            $invoice = Invoice::create([
                'instance_id'     => $instance,
                'customer_id'     => $quotation->customer_id,
                'invoice_number'  => 'INV-' . now()->format('Ymd') . '-' . str_pad(Invoice::where('instance_id', $instance)->count() + 1, 5, '0', STR_PAD_LEFT),
                'subtotal'        => $quotation->subtotal,
                'tax_amount'      => $quotation->tax_amount,
                'discount_amount' => $quotation->discount_amount,
                'total'           => $quotation->total,
                'status'          => 'draft',
                'due_date'        => now()->addDays(config('eshop360.invoice.due_days', 7)),
                'notes'           => $quotation->notes,
                'paid_amount'     => 0,
                'due_amount'      => $quotation->total,
                'created_by'      => auth()->id(),
            ]);

            foreach ($quotation->items as $item) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $item->product_id,
                    'description' => $item->product?->name ?? '',
                    'quantity'    => $item->quantity,
                    'unit_price'  => $item->unit_price,
                    'discount'    => $item->discount ?? 0,
                    'tax'         => $item->tax ?? 0,
                    'total'       => $item->total,
                ]);
            }

            $quotation->update(['status' => 'converted', 'converted_invoice_id' => $invoice->id]);

            return $invoice;
        });

        return redirect()->route('eshop360.invoices.show', ['invoice' => $invoice])
            ->with('success', "Devis converti en facture {$invoice->invoice_number}.");
    }

    /**
     * Download quotation as PDF.
     */
    public function pdf(Quotation $quotation, PdfService $pdf)
    {
        $html = $pdf->quotation($quotation);
        return $pdf->download($html, "devis-{$quotation->reference}.pdf");
    }

    /**
     * Send quotation by email.
     */
    public function sendEmail(Quotation $quotation): RedirectResponse
    {
        $customer = $quotation->customer;
        if (!$customer || !$customer->email) {
            return redirect()->back()->with('error', 'Ce client n\'a pas d\'adresse email.');
        }

        app(\Modules\Eshop360\Services\EmailService::class)->send(
            $customer->email,
            "Devis {$quotation->reference}",
            "<h2>Devis {$quotation->reference}</h2><p>Bonjour {$customer->name},<br>Veuillez trouver ci-joint votre devis d'un montant de <strong>" . number_format($quotation->total, 2) . "</strong>.<br>Valable jusqu'au : {$quotation->valid_until?->format('d/m/Y')}</p>"
        );

        return redirect()->back()->with('success', "Devis envoyé à {$customer->email}.");
    }
}
