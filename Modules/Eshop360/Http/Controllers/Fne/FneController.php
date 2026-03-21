<?php

namespace Modules\Eshop360\Http\Controllers\Fne;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Models\FneInvoice;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Invoice;
use Modules\Eshop360\Services\FneService;

class FneController extends Controller
{
    public function __construct(private FneService $fneService) {}

    /**
     * Sign an Order as FNE invoice.
     */
    public function signOrder(Request $request, string $slug, Order $order)
    {
        $validated = $request->validate([
            'template' => 'nullable|in:B2B,B2C,B2G,B2F',
        ]);

        try {
            $fneInvoice = $this->fneService->signOrder($order, $validated['template'] ?? null);

            return redirect()->back()->with('success', __('Facture FNE editee: :ref', ['ref' => $fneInvoice->fne_reference]));
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Sign an Invoice as FNE.
     */
    public function signInvoice(Request $request, string $slug, Invoice $invoice)
    {
        $validated = $request->validate([
            'template' => 'nullable|in:B2B,B2C,B2G,B2F',
        ]);

        try {
            $fneInvoice = $this->fneService->signInvoice($invoice, $validated['template'] ?? null);

            return redirect()->back()->with('success', __('Facture FNE editee: :ref', ['ref' => $fneInvoice->fne_reference]));
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * List all FNE invoices.
     */
    public function index(Request $request)
    {
        $fneInvoices = FneInvoice::with('invoiceable')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->where('fne_reference', 'like', "%{$s}%"))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('eshop360::fne.index', compact('fneInvoices'));
    }

    /**
     * Show FNE invoice details.
     */
    public function show(string $slug, FneInvoice $fneInvoice)
    {
        $fneInvoice->load('invoiceable', 'signer');
        return view('eshop360::fne.show', compact('fneInvoice'));
    }
}
