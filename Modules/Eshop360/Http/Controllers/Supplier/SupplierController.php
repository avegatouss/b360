<?php

namespace Modules\Eshop360\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Models\Supplier;
use Modules\Eshop360\Services\SupplierService;
use Modules\Core\Support\CurrentInstance;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $instance = CurrentInstance::get();
        $suppliers = Supplier::where('instance_id', $instance->id)
            ->withCount('purchaseOrders')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('company', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%"))
            ->when($request->country, fn ($q, $c) => $q->where('country', $c))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->latest()
            ->paginate(20)
            ->withQueryString();
        return view('eshop360::suppliers.index', compact('suppliers'));
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

    public function show(Supplier $supplier, SupplierService $service)
    {
        $supplier->load('purchaseOrders', 'importOrders');
        $history = $service->getPurchaseHistory($supplier);
        return view('eshop360::suppliers.show', compact('supplier', 'history'));
    }

    public function edit(Supplier $supplier)
    {
        return view('eshop360::suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
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
        return redirect()->back()->with('success', __('eshop::eshop.supplier_updated'));
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->back()->with('success', __('eshop::eshop.supplier_deleted'));
    }

    public function statement(Supplier $supplier, SupplierService $service)
    {
        $history = $service->getPurchaseHistory(
            $supplier,
            request('from'),
            request('to')
        );
        return view('eshop360::suppliers.statement', compact('supplier', 'history'));
    }
}
