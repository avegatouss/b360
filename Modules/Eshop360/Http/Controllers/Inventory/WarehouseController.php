<?php

namespace Modules\Eshop360\Http\Controllers\Inventory;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Store;
use Modules\Eshop360\Models\Warehouse;

class WarehouseController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:eshop.inventory.view')->only('index');
        $this->middleware('can:eshop.inventory.manage')->only(['store', 'storeStore', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        $warehouses = Warehouse::withCount(['stocks', 'stores'])
            ->withSum('stocks', 'quantity')
            ->with('stores')
            ->when($request->search, function ($q, $s) {
                $q->where(function ($warehouseQuery) use ($s) {
                    $warehouseQuery->where('name', 'like', "%{$s}%")
                        ->orWhere('code', 'like', "%{$s}%")
                        ->orWhere('city', 'like', "%{$s}%");
                });
            })
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        // Global stats
        $totalWarehouses = Warehouse::count();
        $activeWarehouses = Warehouse::where('is_active', true)->count();
        $totalStockUnits = \Modules\Eshop360\Models\Stock::sum('quantity');
        $totalProducts = \Modules\Eshop360\Models\Stock::where('quantity', '>', 0)->distinct('product_id')->count('product_id');
        $lowStockCount = \Modules\Eshop360\Models\Stock::where('quantity', '>', 0)
            ->whereRaw('quantity <= (SELECT COALESCE(alert_quantity, 5) FROM eshop_products WHERE eshop_products.id = eshop_stocks.product_id)')
            ->count();

        return view('eshop360::inventory.warehouses.index', compact(
            'warehouses', 'totalWarehouses', 'activeWarehouses', 'totalStockUnits', 'totalProducts', 'lowStockCount'
        ));
    }

    /**
     * Quick-create a store (AJAX from product form).
     */
    public function storeStore(Request $request): JsonResponse|RedirectResponse
    {
        $instance = CurrentInstance::get();

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'warehouse_id' => 'nullable|exists:eshop_warehouses,id',
        ]);

        // Default to first warehouse if not specified
        if (empty($validated['warehouse_id'])) {
            $defaultWarehouse = Warehouse::where('instance_id', $instance->id)->where('is_active', true)->first();
            if (!$defaultWarehouse) {
                $error = __('Veuillez creer un entrepot avant de creer un magasin.');
                return $request->wantsJson()
                    ? response()->json(['message' => $error], 422)
                    : redirect()->back()->with('error', $error);
            }
            $validated['warehouse_id'] = $defaultWarehouse->id;
        }

        $validated['instance_id'] = $instance->id;
        $validated['is_active'] = true;
        $validated['code'] = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $validated['name']), 0, 6)) . rand(100, 999);

        $store = Store::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['id' => $store->id, 'name' => $store->name]);
        }

        return redirect()->back()->with('success', __('Store created successfully.'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $instance = CurrentInstance::get();

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'code'         => 'required|string|max:50|unique:eshop_warehouses,code',
            'address'      => 'nullable|string|max:500',
            'city'         => 'nullable|string|max:100',
            'phone'        => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'is_active'    => 'boolean',
            'stores'              => 'nullable|array',
            'stores.*.name'       => 'required_with:stores|string|max:255',
            'stores.*.code'       => 'required_with:stores|string|max:50',
            'stores.*.address'    => 'nullable|string|max:500',
            'stores.*.phone'      => 'nullable|string|max:30',
            'stores.*.email'      => 'nullable|email|max:255',
            'stores.*.manager_name' => 'nullable|string|max:255',
        ]);

        $validated['instance_id'] = $instance->id;

        $storesData = $validated['stores'] ?? [];
        unset($validated['stores']);

        $warehouse = Warehouse::create($validated);

        foreach ($storesData as $storeData) {
            $storeData['instance_id'] = $instance->id;
            $storeData['warehouse_id'] = $warehouse->id;
            $storeData['is_active'] = true;
            Store::create($storeData);
        }

        if ($request->wantsJson()) {
            return response()->json(['id' => $warehouse->id, 'name' => $warehouse->name]);
        }

        return redirect()->route('eshop360.warehouses.index', $instance->slug)
            ->with('success', __('Warehouse created successfully.'));
    }

    public function update(Request $request, string $slug, Warehouse $warehouse): RedirectResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'code'         => 'required|string|max:50|unique:eshop_warehouses,code,' . $warehouse->id,
            'address'      => 'nullable|string|max:500',
            'city'         => 'nullable|string|max:100',
            'phone'        => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'is_active'    => 'boolean',
        ]);

        $warehouse->update($validated);

        return redirect()->route('eshop360.warehouses.index', $slug)
            ->with('success', __('Warehouse updated successfully.'));
    }

    public function destroy(string $slug, Warehouse $warehouse): RedirectResponse|JsonResponse
    {
        $stockCount = $warehouse->stocks()->where('quantity', '>', 0)->count();

        if ($stockCount > 0) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => __('Cannot delete warehouse with active stock entries.'),
                ], 422);
            }

            return redirect()->route('eshop360.warehouses.index', $slug)
                ->with('error', __('Cannot delete warehouse with active stock entries.'));
        }

        $warehouse->stores()->delete();
        $warehouse->stocks()->delete();
        $warehouse->delete();

        return redirect()->route('eshop360.warehouses.index', $slug)
            ->with('success', __('Warehouse deleted successfully.'));
    }
}
