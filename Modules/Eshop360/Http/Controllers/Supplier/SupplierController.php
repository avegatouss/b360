<?php

namespace Modules\Eshop360\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Models\Supplier;
use Modules\Eshop360\Services\SupplierService;
use Modules\Core\Support\CurrentInstance;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $instance = CurrentInstance::get();
        $instanceId = $instance->id;

        $suppliers = Supplier::where('instance_id', $instanceId)
            ->withCount('purchaseOrders')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('company', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%"))
            ->when($request->country, fn ($q, $c) => $q->where('country', $c))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Stats achats par fournisseur
        $purchaseStats = DB::table('eshop_purchase_orders')
            ->join('eshop_suppliers', 'eshop_purchase_orders.supplier_id', '=', 'eshop_suppliers.id')
            ->where('eshop_purchase_orders.instance_id', $instanceId)
            ->where('eshop_purchase_orders.status', '!=', 'cancelled')
            ->selectRaw('
                eshop_suppliers.id as supplier_id, eshop_suppliers.name as supplier_name,
                COUNT(*) as order_count,
                SUM(eshop_purchase_orders.total) as total_purchases,
                SUM(eshop_purchase_orders.paid_amount) as total_paid,
                SUM(eshop_purchase_orders.due_amount) as total_due,
                SUM(CASE WHEN eshop_purchase_orders.status = "received" THEN 1 ELSE 0 END) as received_count
            ')
            ->groupBy('eshop_suppliers.id', 'eshop_suppliers.name')
            ->orderByDesc('total_purchases')
            ->get();

        // Ventes des produits de chaque fournisseur
        $salesBySupplier = DB::table('eshop_order_items')
            ->join('eshop_orders', 'eshop_order_items.order_id', '=', 'eshop_orders.id')
            ->join('eshop_products', 'eshop_order_items.product_id', '=', 'eshop_products.id')
            ->where('eshop_orders.instance_id', $instanceId)
            ->where('eshop_orders.status', 'completed')
            ->whereNotNull('eshop_products.supplier_id')
            ->selectRaw('
                eshop_products.supplier_id,
                SUM(eshop_order_items.quantity) as qty_sold,
                SUM(eshop_order_items.total) as revenue
            ')
            ->groupBy('eshop_products.supplier_id')
            ->get()
            ->keyBy('supplier_id');

        return view('eshop360::suppliers.index', compact('suppliers', 'purchaseStats', 'salesBySupplier'));
    }

    public function create()
    {
        return view('eshop360::suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        $validated['instance_id'] = CurrentInstance::get()->id;
        Supplier::create($validated);
        return redirect()->back()->with('success', __('eshop::eshop.supplier_created'));
    }

    public function show(string $slug, Supplier $supplier, SupplierService $service)
    {
        $supplier->load('purchaseOrders', 'importOrders');
        $history = $service->getPurchaseHistory($supplier);
        return view('eshop360::suppliers.show', compact('supplier', 'history'));
    }

    public function edit(string $slug, Supplier $supplier)
    {
        return view('eshop360::suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, string $slug, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $supplier->update($validated);
        return redirect()->back()->with('success', __('Fournisseur mis a jour.'));
    }

    public function destroy(string $slug, Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('eshop360.suppliers.index', $slug)->with('success', __('Fournisseur supprime.'));
    }

    public function statement(string $slug, Supplier $supplier, SupplierService $service)
    {
        $history = $service->getPurchaseHistory(
            $supplier,
            request('from'),
            request('to')
        );
        return view('eshop360::suppliers.statement', compact('supplier', 'history'));
    }
}
