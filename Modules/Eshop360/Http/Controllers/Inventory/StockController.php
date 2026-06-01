<?php

namespace Modules\Eshop360\Http\Controllers\Inventory;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\Inventory\Models\Stock;
use Modules\Eshop360\Domain\Inventory\Models\StockMovement;
use Modules\Eshop360\Domain\Inventory\Models\Store;
use Modules\Eshop360\Domain\Inventory\Models\Warehouse;

class StockController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:eshop.inventory.view');
        $this->middleware('can:eshop.inventory.manage')->only(['store', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        $stocks = Stock::with(['product', 'warehouse', 'store'])
            ->when($request->warehouse_id, fn ($q, $w) => $q->where('warehouse_id', $w))
            ->when($request->store_id, fn ($q, $s) => $q->where('store_id', $s))
            ->when($request->search, fn ($q, $s) => $q->whereHas('product', function ($pq) use ($s) {
                $pq->where('name', 'like', "%{$s}%")
                    ->orWhere('sku', 'like', "%{$s}%");
            }))
            ->when($request->filled('low_stock'), fn ($q) => $q->whereColumn('quantity', '<=', DB::raw(
                '(SELECT alert_quantity FROM eshop_products WHERE eshop_products.id = eshop_stocks.product_id)'
            )))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $stores = Store::where('is_active', true)->orderBy('name')->get();
        $products = Product::orderBy('name')->limit(1200)->get();

        return view('eshop360::inventory.stocks.index', compact('stocks', 'warehouses', 'stores', 'products'));
    }

    public function store(Request $request, string $slug)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:eshop_products,id',
            'warehouse_id' => 'required|exists:eshop_warehouses,id',
            'store_id' => 'nullable|exists:eshop_stores,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $instanceId = CurrentInstance::get()?->id;

        DB::transaction(function () use ($validated, $instanceId) {
            $stock = Stock::firstOrCreate(
                [
                    'instance_id' => $instanceId,
                    'product_id' => $validated['product_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'store_id' => $validated['store_id'] ?? null,
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                ]
            );

            $stock->increment('quantity', $validated['quantity']);

            StockMovement::create([
                'instance_id' => $instanceId,
                'product_id' => $stock->product_id,
                'warehouse_id' => $stock->warehouse_id,
                'store_id' => $stock->store_id,
                'type' => 'in',
                'quantity' => $validated['quantity'],
                'notes' => $validated['notes'] ?? 'Stock added',
                'performed_by' => auth()->id(),
            ]);
        });

        return redirect()->route('eshop360.stocks.index', $slug)
            ->with('success', __('Stock added successfully.'));
    }

    public function update(Request $request, string $slug, Stock $stock)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'reserved_quantity' => 'nullable|integer|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        $previousQuantity = $stock->quantity;
        $newQuantity = $validated['quantity'];
        $difference = $newQuantity - $previousQuantity;

        DB::transaction(function () use ($stock, $validated, $difference) {
            $stock->update([
                'quantity' => $validated['quantity'],
                'reserved_quantity' => $validated['reserved_quantity'] ?? $stock->reserved_quantity,
            ]);

            if ($difference !== 0) {
                StockMovement::create([
                    'instance_id' => $stock->instance_id,
                    'product_id' => $stock->product_id,
                    'warehouse_id' => $stock->warehouse_id,
                    'store_id' => $stock->store_id,
                    'type' => 'adjustment',
                    'quantity' => $difference,
                    'notes' => $validated['reason'] ?? 'Manual stock update',
                    'performed_by' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('eshop360.stocks.index', $slug)
            ->with('success', __('Stock updated successfully.'));
    }

    public function destroy(string $slug, Stock $stock)
    {
        if ($stock->quantity > 0 || $stock->reserved_quantity > 0) {
            return redirect()->route('eshop360.stocks.index', $slug)
                ->with('error', __('Only empty stock lines can be deleted.'));
        }

        $stock->delete();

        return redirect()->route('eshop360.stocks.index', $slug)
            ->with('success', __('Stock line deleted successfully.'));
    }

    public function lowStock(Request $request)
    {
        $productsQuery = Product::with(['stocks.warehouse', 'category', 'brand'])
            ->whereExists(function ($q) use ($request) {
                $q->select(DB::raw(1))
                    ->from('eshop_stocks')
                    ->whereColumn('eshop_stocks.product_id', 'eshop_products.id')
                    ->whereRaw('eshop_stocks.quantity <= eshop_products.alert_quantity')
                    ->when($request->warehouse_id, fn ($q, $w) => $q->where('eshop_stocks.warehouse_id', $w));
            })
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"));

        $hasQuantityFilter = $request->filled('min_quantity') || $request->filled('max_quantity');

        if ($hasQuantityFilter) {
            $productsQuery = $productsQuery
                ->join('eshop_stocks as all_stocks', 'all_stocks.product_id', '=', 'eshop_products.id')
                ->when($request->warehouse_id, fn ($q, $w) => $q->where('all_stocks.warehouse_id', $w))
                ->groupBy('eshop_products.id')
                ->select('eshop_products.*')
                ->when($request->filled('min_quantity'), fn ($q, $min) => $q->havingRaw('SUM(all_stocks.quantity) >= ?', [$min]))
                ->when($request->filled('max_quantity'), fn ($q, $max) => $q->havingRaw('SUM(all_stocks.quantity) <= ?', [$max]));
        }

        $products = $productsQuery
            ->latest('eshop_products.created_at')
            ->paginate(30)
            ->withQueryString();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('eshop360::inventory.stocks.low', compact('products', 'warehouses'));
    }

    public function expired(Request $request)
    {
        $products = Product::with(['stocks.warehouse', 'category'])
            ->expired()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('eshop360::inventory.stocks.expired', compact('products'));
    }

    public function expiryReport(Request $request)
    {
        $days = $request->integer('days', 30);

        $expiringSoon = Product::with(['stocks.warehouse', 'category'])
            ->expiringSoon($days)
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('expiry_date')
            ->paginate(30)
            ->withQueryString();

        $expiredCount = Product::expired()->count();
        $expiringCount = Product::expiringSoon($days)->count();

        return view('eshop360::inventory.stocks.expiry-report', compact('expiringSoon', 'expiredCount', 'expiringCount', 'days'));
    }

    public function quantityAlert(Request $request)
    {
        $products = Product::with(['stocks.warehouse'])
            ->whereHas('stocks', function ($q) {
                $q->whereRaw('quantity <= '.DB::raw(
                    '(SELECT alert_quantity FROM eshop_products WHERE eshop_products.id = eshop_stocks.product_id)'
                ));
            })
            ->when($request->warehouse_id, fn ($q, $w) => $q->whereHas('stocks', function ($sq) use ($w) {
                $sq->where('warehouse_id', $w);
            }))
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('eshop360::inventory.stocks.quantity-alert', compact('products', 'warehouses'));
    }
}
