<?php

namespace Modules\Eshop360\Http\Controllers\Printing;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Communication\Models\ReceiptTemplate;
use Modules\Eshop360\Domain\Inventory\Models\Store;

class ReceiptTemplateController extends Controller
{
    /**
     * List all receipt templates for the current instance.
     */
    public function index()
    {
        $instance = CurrentInstance::get();
        $templates = ReceiptTemplate::with('store')
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->paginate(20);

        return view('eshop360::printing.receipt-templates.index', compact('instance', 'templates'));
    }

    /**
     * Show the form to create a new receipt template.
     */
    public function create()
    {
        $instance = CurrentInstance::get();
        $stores = Store::orderBy('name')->pluck('name', 'id');

        return view('eshop360::printing.receipt-templates.create', compact('instance', 'stores'));
    }

    /**
     * Store a newly created receipt template.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'store_id' => 'nullable|exists:eshop_stores,id',
            'header_text' => 'nullable|string|max:2000',
            'footer_text' => 'nullable|string|max:2000',
            'show_logo' => 'boolean',
            'show_address' => 'boolean',
            'show_phone' => 'boolean',
            'paper_width' => 'required|in:58mm,80mm',
            'font_size' => 'required|in:small,normal,large',
            'is_default' => 'boolean',
        ]);

        $instance = CurrentInstance::get();
        $validated['instance_id'] = $instance->id;

        // If setting as default, unset other defaults for this instance
        if (! empty($validated['is_default'])) {
            ReceiptTemplate::where('instance_id', $instance->id)
                ->when($validated['store_id'] ?? null, fn ($q, $sid) => $q->where('store_id', $sid))
                ->update(['is_default' => false]);
        }

        ReceiptTemplate::create($validated);

        return redirect()->route('eshop360.receipt-templates.index')
            ->with('success', __('Receipt template created successfully.'));
    }

    /**
     * Show the form to edit an existing receipt template.
     */
    public function edit(int $id)
    {
        $instance = CurrentInstance::get();
        $template = ReceiptTemplate::findOrFail($id);
        $stores = Store::orderBy('name')->pluck('name', 'id');

        return view('eshop360::printing.receipt-templates.edit', compact('instance', 'template', 'stores'));
    }

    /**
     * Update the specified receipt template.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $template = ReceiptTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'store_id' => 'nullable|exists:eshop_stores,id',
            'header_text' => 'nullable|string|max:2000',
            'footer_text' => 'nullable|string|max:2000',
            'show_logo' => 'boolean',
            'show_address' => 'boolean',
            'show_phone' => 'boolean',
            'paper_width' => 'required|in:58mm,80mm',
            'font_size' => 'required|in:small,normal,large',
            'is_default' => 'boolean',
        ]);

        $instance = CurrentInstance::get();

        if (! empty($validated['is_default'])) {
            ReceiptTemplate::where('instance_id', $instance->id)
                ->where('id', '!=', $template->id)
                ->when($validated['store_id'] ?? null, fn ($q, $sid) => $q->where('store_id', $sid))
                ->update(['is_default' => false]);
        }

        $template->update($validated);

        return redirect()->route('eshop360.receipt-templates.index')
            ->with('success', __('Receipt template updated successfully.'));
    }

    /**
     * Delete the specified receipt template.
     */
    public function destroy(int $id): RedirectResponse
    {
        $template = ReceiptTemplate::findOrFail($id);
        $template->delete();

        return redirect()->route('eshop360.receipt-templates.index')
            ->with('success', __('Receipt template deleted successfully.'));
    }

    /**
     * Preview a receipt template with sample data.
     */
    public function preview(int $id)
    {
        $template = ReceiptTemplate::findOrFail($id);
        $instance = CurrentInstance::get();

        // Build sample order data for preview
        $sampleOrder = (object) [
            'reference' => 'ORD-SAMPLE-001',
            'created_at' => now(),
            'customer' => (object) ['name' => 'Client Exemple'],
            'store' => (object) ['name' => 'Magasin Principal'],
            'items' => collect([
                (object) [
                    'product' => (object) ['name' => 'Produit A'],
                    'description' => 'Produit A',
                    'quantity' => 2,
                    'unit_price' => 1500,
                    'total' => 3000,
                ],
                (object) [
                    'product' => (object) ['name' => 'Produit B'],
                    'description' => 'Produit B',
                    'quantity' => 1,
                    'unit_price' => 5000,
                    'total' => 5000,
                ],
                (object) [
                    'product' => (object) ['name' => 'Produit C'],
                    'description' => 'Produit C',
                    'quantity' => 3,
                    'unit_price' => 750,
                    'total' => 2250,
                ],
            ]),
            'subtotal' => 10250,
            'tax_amount' => 0,
            'discount_amount' => 250,
            'total' => 10000,
            'paid_amount' => 10000,
            'payment_method' => 'Especes',
            'cashier' => null,
            'creator' => null,
            'payments' => collect([]),
        ];

        return view('eshop360::printing.receipt-templates.preview', compact('template', 'instance', 'sampleOrder'));
    }
}
