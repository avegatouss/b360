<?php

namespace Modules\Eshop360\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\Inventory\Models\Warehouse;
use Modules\Eshop360\Domain\Purchasing\Models\ImportCost;
use Modules\Eshop360\Domain\Purchasing\Models\ImportCostType;
use Modules\Eshop360\Domain\Purchasing\Models\ImportOrder;
use Modules\Eshop360\Domain\Purchasing\Models\Supplier;
use Modules\Eshop360\Services\ImportService;
use Modules\Eshop360\Services\StockService;

class ImportController extends Controller
{
    public function __construct(private ImportService $importService) {}

    public function index(Request $request)
    {
        $instance = CurrentInstance::get();

        $query = ImportOrder::where('instance_id', $instance->id)
            ->with('supplier', 'warehouse', 'creator')
            ->withCount('items')
            ->when($request->search, fn ($q, $s) => $q->where('reference', 'like', "%{$s}%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->supplier_id, fn ($q, $s) => $q->where('supplier_id', $s))
            ->when($request->warehouse_id, fn ($q, $w) => $q->where('warehouse_id', $w))
            ->when($request->shipping_type, fn ($q, $t) => $q->where('shipping_type', $t))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));

        // KPIs
        $allImports = ImportOrder::where('instance_id', $instance->id);
        $kpiTotal = (clone $allImports)->count();
        $kpiDraft = (clone $allImports)->where('status', 'draft')->count();
        $kpiShipped = (clone $allImports)->where('status', 'shipped')->count();
        $kpiReceived = (clone $allImports)->where('status', 'received')->count();
        $kpiInTransit = (clone $allImports)->whereIn('status', ['ordered', 'shipped', 'customs'])->count();

        $imports = $query->latest()->paginate(20)->withQueryString();

        $suppliers = Supplier::where('instance_id', $instance->id)->orderBy('name')->get(['id', 'name']);
        $warehouses = Warehouse::where('instance_id', $instance->id)->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('eshop360::imports.index', compact(
            'imports', 'suppliers', 'warehouses',
            'kpiTotal', 'kpiDraft', 'kpiShipped', 'kpiReceived', 'kpiInTransit'
        ));
    }

    public function create()
    {
        $instance = CurrentInstance::get();
        $suppliers = Supplier::where('instance_id', $instance->id)->where('is_active', true)->get();
        $warehouses = Warehouse::where('instance_id', $instance->id)->where('is_active', true)->get();
        $products = Product::where('instance_id', $instance->id)->where('is_active', true)->get();

        return view('eshop360::imports.create', compact('suppliers', 'warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:eshop_suppliers,id',
            'warehouse_id' => 'required|exists:eshop_warehouses,id',
            'shipping_type' => 'required|in:sea,air,land',
            'container_no' => 'nullable|string|max:100',
            'ship_date' => 'nullable|date',
            'eta' => 'nullable|date|after_or_equal:ship_date',
            'cost_allocation_method' => 'required|in:value,quantity,hybrid',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:eshop_products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price_factory' => 'required|numeric|min:0',
        ]);

        $instance = CurrentInstance::get();
        $validated['instance_id'] = $instance->id;
        $validated['reference'] = $this->importService->generateReference($instance->id);
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'draft';

        $items = $validated['items'];
        unset($validated['items']);

        $order = $this->importService->createImportOrder($validated, $items);

        return redirect()->route('eshop360.imports.show', [$instance->slug, $order->id])
            ->with('success', __('eshop::eshop.import_created'));
    }

    public function show(string $slug, ImportOrder $import)
    {
        $import->load('supplier', 'warehouse', 'items.product', 'costs', 'creator');

        return view('eshop360::imports.show', compact('import'));
    }

    public function update(Request $request, string $slug, ImportOrder $import)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:draft,confirmed,shipped,customs,received,cancelled',
            'container_no' => 'nullable|string|max:100',
            'ship_date' => 'nullable|date',
            'eta' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        $import->update($validated);

        return redirect()->back()->with('success', __('eshop::eshop.import_updated'));
    }

    public function addCost(Request $request, string $slug, ImportOrder $import)
    {
        $validated = $request->validate([
            'type' => 'required|in:freight,customs,tax,admin,local_transport,handling,storage,other',
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        $this->importService->addCost($import, $validated);

        return redirect()->back()->with('success', __('eshop::eshop.cost_added'));
    }

    public function removeCost(string $slug, ImportOrder $import, ImportCost $cost)
    {
        $cost->delete();

        return redirect()->back()->with('success', __('eshop::eshop.cost_removed'));
    }

    public function allocateCosts(string $slug, ImportOrder $import)
    {
        $this->importService->allocateCosts($import);

        return redirect()->back()->with('success', __('eshop::eshop.costs_allocated'));
    }

    public function receive(string $slug, ImportOrder $import, StockService $stockService)
    {
        $this->importService->receiveImport($import, $stockService);

        return redirect()->back()->with('success', __('eshop::eshop.import_received'));
    }

    public function simulate(string $slug, ImportOrder $import)
    {
        $import->load(['items.product', 'costs', 'supplier', 'warehouse']);
        $importService = app(ImportService::class);

        $simValue = $importService->simulateAllocation($import, 'value');
        $simQuantity = $importService->simulateAllocation($import, 'quantity');
        $simHybrid = $importService->simulateAllocation($import, 'hybrid');

        return view('eshop360::imports.simulate', compact('import', 'simValue', 'simQuantity', 'simHybrid'));
    }

    public function destroy(string $slug, ImportOrder $import)
    {
        $import->delete();

        return redirect()->back()->with('success', __('eshop::eshop.import_deleted'));
    }

    public function storeCostType(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:100',
        ]);

        $instance = CurrentInstance::get();
        $code = \Illuminate\Support\Str::slug($validated['label'], '_');

        $type = ImportCostType::updateOrCreate(
            ['instance_id' => $instance->id, 'code' => $code],
            ['label' => $validated['label'], 'is_active' => true]
        );

        if ($request->wantsJson()) {
            return response()->json(['id' => $type->id, 'code' => $type->code, 'label' => $type->label]);
        }

        return redirect()->back()->with('success', __('Type de frais cree.'));
    }
}
