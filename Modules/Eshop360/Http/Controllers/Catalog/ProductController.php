<?php

namespace Modules\Eshop360\Http\Controllers\Catalog;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Catalog\Models\Brand;
use Modules\Eshop360\Domain\Catalog\Models\Category;
use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\Catalog\Models\Tax;
use Modules\Eshop360\Domain\Inventory\Models\Stock;
use Modules\Eshop360\Domain\Inventory\Models\StockMovement;
use Modules\Eshop360\Domain\Inventory\Models\Store;
use Modules\Eshop360\Domain\Inventory\Models\Warehouse;
use Modules\Eshop360\Services\ChannelAccessService;
use Modules\Eshop360\Services\ChargesService;
use Modules\Eshop360\Services\UserResourceScopeService;
use Modules\Eshop360\Support\CurrentChannel;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['category', 'brand', 'stocks'])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('sku', 'like', "%{$s}%")
                ->orWhere('barcode', 'like', "%{$s}%"))
            ->when($request->category_id, fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->brand_id, fn ($q, $b) => $q->where('brand_id', $b))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->store_id, fn ($q, $s) => $q->whereHas('stocks', fn ($sq) => $sq->where('store_id', $s)))
            ->when($request->warehouse_id, fn ($q, $w) => $q->whereHas('stocks', fn ($sq) => $sq->where('warehouse_id', $w)))
            ->when($request->price_min, fn ($q, $min) => $q->where('price', '>=', $min))
            ->when($request->price_max, fn ($q, $max) => $q->where('price', '<=', $max))
            ->when(
                app(UserResourceScopeService::class)->hasWarehouseAssignments()
                || app(UserResourceScopeService::class)->hasStoreAssignments(),
                function ($query) {
                    $scope = app(UserResourceScopeService::class);
                    $query->whereHas('stocks', function ($sq) use ($scope) {
                        $scope->applyWarehouseScope($sq, 'warehouse_id');
                        if ($scope->hasStoreAssignments()) {
                            $scope->applyStoreScope($sq, 'store_id');
                        }
                    });
                }
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $categories = Category::active()->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $stores = Store::orderBy('name')->get(['id', 'name']);
        $warehouses = Warehouse::orderBy('name')->get(['id', 'name']);

        return view('eshop360::catalog.products.index', compact('products', 'categories', 'brands', 'stores', 'warehouses'));
    }

    public function create()
    {
        $categories = Category::active()->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $stores = Store::orderBy('name')->get(['id', 'name']);
        $warehouses = Warehouse::orderBy('name')->get(['id', 'name']);
        $taxes = Tax::where('is_active', true)->orderBy('name')->get();

        return view('eshop360::catalog.products.create', compact('categories', 'brands', 'stores', 'warehouses', 'taxes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:eshop_products,sku',
            'category_id' => 'nullable|exists:eshop_categories,id',
            'brand_id' => 'nullable|exists:eshop_brands,id',
            'description' => 'nullable|string|max:50000',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax_inclusive' => 'boolean',
            'discount_type' => 'nullable|in:none,percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:20',
            'min_quantity' => 'nullable|integer|min:0',
            'alert_quantity' => 'nullable|integer|min:0',
            'barcode' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|max:2048',
            'expiry_date' => 'nullable|date',
            'manufactured_date' => 'nullable|date|before_or_equal:today',
            'is_active' => 'boolean',
            'purchase_price_factory' => 'nullable|numeric|min:0',
            'purchase_price_provisional' => 'nullable|numeric|min:0',
            'pght' => 'nullable|numeric|min:0',
            'cost_price_real' => 'nullable|numeric|min:0',
            'selling_type' => 'nullable|in:pos,online,both',
            'taxes' => 'nullable|array',
            'taxes.*' => 'exists:eshop_taxes,id',
            'stocks' => 'nullable|array',
            'stocks.*.store_id' => 'nullable|exists:eshop_stores,id',
            'stocks.*.warehouse_id' => 'nullable|exists:eshop_warehouses,id',
            'stocks.*.quantity' => 'nullable|integer|min:0',
            'stocks.*.alert_quantity' => 'nullable|integer|min:0',
            'stocks.*.min_quantity' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $instanceId = CurrentInstance::get()?->id ?? $request->route('instance_id') ?? session('instance_id');
        $validated['instance_id'] = $instanceId;
        $validated['created_by'] = auth()->id();
        $validated['cost_price'] = $validated['cost_price'] ?? 0;
        $validated['tax_rate'] = $validated['tax_rate'] ?? 0;
        $validated['discount_type'] = $validated['discount_type'] ?? 'none';
        $validated['discount_value'] = $validated['discount_value'] ?? 0;
        $validated['unit'] = $validated['unit'] ?? 'pcs';
        $validated['selling_type'] = $validated['selling_type'] ?? 'both';

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        if ($request->hasFile('images')) {
            $paths = [];
            foreach ($request->file('images') as $file) {
                $paths[] = $file->store('products/gallery', 'public');
            }
            $validated['images'] = $paths;
        }

        $taxes = $validated['taxes'] ?? [];
        $stocks = $validated['stocks'] ?? [];
        unset($validated['taxes'], $validated['stocks']);

        $product = Product::create($validated);

        // Create stock records per store/warehouse
        foreach ($stocks as $stockRow) {
            if (empty($stockRow['store_id']) && empty($stockRow['warehouse_id'])) {
                continue;
            }
            Stock::create([
                'instance_id' => $instanceId,
                'product_id' => $product->id,
                'store_id' => $stockRow['store_id'] ?: null,
                'warehouse_id' => $stockRow['warehouse_id'] ?: null,
                'quantity' => (int) ($stockRow['quantity'] ?? 0),
            ]);
        }
        // Save alert/min from first stock row to product
        if (! empty($stocks[0])) {
            $product->update([
                'alert_quantity' => $stocks[0]['alert_quantity'] ?? $product->alert_quantity,
                'min_quantity' => $stocks[0]['min_quantity'] ?? $product->min_quantity,
            ]);
        }

        if (! empty($taxes)) {
            $taxData = [];
            foreach ($taxes as $taxId) {
                $taxData[$taxId] = ['type' => $validated['tax_inclusive'] ? 'inclusive' : 'exclusive'];
            }
            foreach ($taxData as $taxId => $pivot) {
                \Modules\Eshop360\Domain\Catalog\Models\ProductTax::create([
                    'product_id' => $product->id,
                    'tax_id' => $taxId,
                    'type' => $pivot['type'],
                ]);
            }
        }

        return redirect()->route('eshop360.products.index', ['slug' => $request->route('slug')])
            ->with('success', __('Produit cree avec succes.'));
    }

    public function show(string $slug, Product $product)
    {
        // Channel context for raw queries
        $channelId = CurrentChannel::isScoped() ? CurrentChannel::id() : null;
        $accessibleIds = app(ChannelAccessService::class)->accessibleChannelIds(auth()->user());

        // 1. Load product with comprehensive relations
        $scope = app(UserResourceScopeService::class);
        $product->load([
            'category',
            'brand',
            'stocks' => function ($q) use ($scope) {
                if ($scope->hasWarehouseAssignments()) {
                    $scope->applyWarehouseScope($q, 'warehouse_id');
                }
                if ($scope->hasStoreAssignments()) {
                    $scope->applyStoreScope($q, 'store_id');
                }
                $q->with(['warehouse', 'store']);
            },
            'creator',
            'variations',
            'productTaxes.tax',
        ]);

        // 2. Total and reserved stock
        $totalStock = $product->stocks->sum('quantity');
        $reservedStock = $product->stocks->sum('reserved_quantity');

        // 3. Stock movements history (last 50)
        $stockMovements = StockMovement::where('product_id', $product->id)
            ->with(['warehouse', 'store', 'performer'])
            ->latest()
            ->limit(50)
            ->get();

        // 4. Sales statistics from completed orders
        $salesStats = DB::table('eshop_order_items')
            ->join('eshop_orders', 'eshop_order_items.order_id', '=', 'eshop_orders.id')
            ->where('eshop_order_items.product_id', $product->id)
            ->where('eshop_orders.status', 'completed')
            ->when($channelId, fn ($q) => $q->where('eshop_orders.channel_id', $channelId))
            ->when(! $channelId && $accessibleIds !== null, fn ($q) => $q->whereIn('eshop_orders.channel_id', $accessibleIds->all()))
            ->selectRaw('
                COALESCE(SUM(eshop_order_items.quantity), 0) as total_quantity_sold,
                COALESCE(SUM(eshop_order_items.total), 0) as total_revenue,
                COUNT(DISTINCT eshop_order_items.order_id) as order_count
            ')
            ->first();

        $totalQuantitySold = (int) $salesStats->total_quantity_sold;
        $totalRevenue = (float) $salesStats->total_revenue;
        $orderCount = (int) $salesStats->order_count;
        $averageSellingPrice = $totalQuantitySold > 0
            ? round($totalRevenue / $totalQuantitySold, 2)
            : 0;

        // Monthly sales for last 6 months
        $monthlySales = DB::table('eshop_order_items')
            ->join('eshop_orders', 'eshop_order_items.order_id', '=', 'eshop_orders.id')
            ->where('eshop_order_items.product_id', $product->id)
            ->where('eshop_orders.status', 'completed')
            ->where('eshop_orders.created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->when($channelId, fn ($q) => $q->where('eshop_orders.channel_id', $channelId))
            ->when(! $channelId && $accessibleIds !== null, fn ($q) => $q->whereIn('eshop_orders.channel_id', $accessibleIds->all()))
            ->selectRaw('
                YEAR(eshop_orders.created_at) as year,
                MONTH(eshop_orders.created_at) as month,
                COALESCE(SUM(eshop_order_items.quantity), 0) as quantity,
                COALESCE(SUM(eshop_order_items.total), 0) as revenue
            ')
            ->groupByRaw('YEAR(eshop_orders.created_at), MONTH(eshop_orders.created_at)')
            ->orderByRaw('YEAR(eshop_orders.created_at), MONTH(eshop_orders.created_at)')
            ->get();

        // 5. Charges coverage analysis
        $instanceId = CurrentInstance::get()?->id ?? session('instance_id');
        $chargesService = app(ChargesService::class);
        $totalMonthlyCharges = $chargesService->getTotalCostPerSecond((int) $instanceId) * ChargesService::SECONDS_PER_MONTH;

        $totalStockValue = $totalStock * (float) $product->price;
        $costStockValue = $totalStock * (float) $product->cost_price;
        $marginPerUnit = (float) $product->price - (float) $product->cost_price;

        // Average monthly revenue from this product (last 3 months)
        $revenueLastThreeMonths = DB::table('eshop_order_items')
            ->join('eshop_orders', 'eshop_order_items.order_id', '=', 'eshop_orders.id')
            ->where('eshop_order_items.product_id', $product->id)
            ->where('eshop_orders.status', 'completed')
            ->where('eshop_orders.created_at', '>=', now()->subMonths(3)->startOfMonth())
            ->when($channelId, fn ($q) => $q->where('eshop_orders.channel_id', $channelId))
            ->when(! $channelId && $accessibleIds !== null, fn ($q) => $q->whereIn('eshop_orders.channel_id', $accessibleIds->all()))
            ->sum('eshop_order_items.total');

        $monthlyRevenueAvg = (float) $revenueLastThreeMonths / 3;

        $chargeCoveragePercentage = $totalMonthlyCharges > 0
            ? round(($monthlyRevenueAvg / $totalMonthlyCharges) * 100, 2)
            : 0;

        $projectedRevenueAllStock = $totalStock * (float) $product->price;
        $projectedChargeCoverage = $totalMonthlyCharges > 0
            ? round(($projectedRevenueAllStock / $totalMonthlyCharges) * 100, 2)
            : 0;

        $chargesCoverage = [
            'total_monthly_charges' => round($totalMonthlyCharges, 2),
            'total_stock_value' => round($totalStockValue, 2),
            'cost_stock_value' => round($costStockValue, 2),
            'margin_per_unit' => round($marginPerUnit, 2),
            'monthly_revenue_avg' => round($monthlyRevenueAvg, 2),
            'charge_coverage_pct' => $chargeCoveragePercentage,
            'projected_revenue_all' => round($projectedRevenueAllStock, 2),
            'projected_charge_coverage' => $projectedChargeCoverage,
        ];

        // 6. Stock by store/warehouse breakdown
        $stockByLocation = $product->stocks->map(fn ($stock) => [
            'id' => $stock->id,
            'store' => $stock->store?->name,
            'store_id' => $stock->store_id,
            'warehouse' => $stock->warehouse?->name,
            'warehouse_id' => $stock->warehouse_id,
            'quantity' => $stock->quantity,
            'reserved' => $stock->reserved_quantity ?? 0,
        ]);

        // JSON response for AJAX
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'product' => $product,
                'total_stock' => $totalStock,
                'reserved_stock' => $reservedStock,
                'stock_movements' => $stockMovements,
                'sales_stats' => [
                    'total_qty_sold' => $totalQuantitySold,
                ],
                'sales_statistics' => [
                    'total_quantity_sold' => $totalQuantitySold,
                    'total_revenue' => $totalRevenue,
                    'average_selling_price' => $averageSellingPrice,
                    'order_count' => $orderCount,
                    'monthly_sales' => $monthlySales,
                ],
                'charges_coverage' => $chargesCoverage,
                'stock_by_location' => $stockByLocation,
            ]);
        }

        return view('eshop360::catalog.products.show', compact(
            'product',
            'totalStock',
            'reservedStock',
            'stockMovements',
            'totalQuantitySold',
            'totalRevenue',
            'averageSellingPrice',
            'orderCount',
            'monthlySales',
            'chargesCoverage',
            'stockByLocation',
        ));
    }

    public function edit(string $slug, Product $product)
    {
        $product->load('productTaxes');
        $categories = Category::active()->orderBy('name')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $stores = Store::orderBy('name')->get(['id', 'name']);
        $warehouses = Warehouse::orderBy('name')->get(['id', 'name']);
        $taxes = Tax::where('is_active', true)->orderBy('name')->get();

        return view('eshop360::catalog.products.edit', compact('product', 'categories', 'brands', 'stores', 'warehouses', 'taxes'));
    }

    public function update(Request $request, string $slug, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:eshop_products,sku,'.$product->id,
            'category_id' => 'nullable|exists:eshop_categories,id',
            'brand_id' => 'nullable|exists:eshop_brands,id',
            'description' => 'nullable|string|max:50000',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax_inclusive' => 'boolean',
            'discount_type' => 'nullable|in:none,percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:20',
            'min_quantity' => 'nullable|integer|min:0',
            'alert_quantity' => 'nullable|integer|min:0',
            'barcode' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|max:2048',
            'expiry_date' => 'nullable|date',
            'manufactured_date' => 'nullable|date|before_or_equal:today',
            'is_active' => 'boolean',
            'purchase_price_factory' => 'nullable|numeric|min:0',
            'purchase_price_provisional' => 'nullable|numeric|min:0',
            'pght' => 'nullable|numeric|min:0',
            'cost_price_real' => 'nullable|numeric|min:0',
            'taxes' => 'nullable|array',
            'taxes.*' => 'exists:eshop_taxes,id',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['tax_rate'] = $validated['tax_rate'] ?? 0;
        $validated['discount_type'] = $validated['discount_type'] ?? 'none';
        $validated['unit'] = $validated['unit'] ?? 'pcs';
        $validated['selling_type'] = $validated['selling_type'] ?? 'both';

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        if ($request->hasFile('images')) {
            $paths = [];
            foreach ($request->file('images') as $file) {
                $paths[] = $file->store('products/gallery', 'public');
            }
            $validated['images'] = $paths;
        }

        $taxes = $validated['taxes'] ?? [];
        unset($validated['taxes']);

        $product->update($validated);

        // Sync product taxes
        \Modules\Eshop360\Domain\Catalog\Models\ProductTax::where('product_id', $product->id)->delete();
        if (! empty($taxes)) {
            $taxData = [];
            foreach ($taxes as $taxId) {
                $taxData[$taxId] = ['type' => $validated['tax_inclusive'] ? 'inclusive' : 'exclusive'];
            }
            foreach ($taxData as $taxId => $pivot) {
                \Modules\Eshop360\Domain\Catalog\Models\ProductTax::create([
                    'product_id' => $product->id,
                    'tax_id' => $taxId,
                    'type' => $pivot['type'],
                ]);
            }
        }

        return redirect()->route('eshop360.products.index')
            ->with('success', __('Product updated successfully.'));
    }

    public function destroy(string $slug, Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('eshop360.products.index')
            ->with('success', __('Product deleted successfully.'));
    }

    /**
     * Search product by barcode, SKU or name (JSON endpoint for POS scanner).
     */
    public function search(Request $request)
    {
        $query = trim($request->get('barcode') ?? $request->get('q') ?? '');

        if (empty($query)) {
            return response()->json(['product' => null]);
        }

        $product = Product::with(['stocks' => function ($q) use ($request) {
            if ($request->warehouse_id) {
                $q->where('warehouse_id', $request->warehouse_id);
            }
        }])
            ->where(function ($q) use ($query) {
                $q->where('barcode', $query)
                    ->orWhere('sku', $query)
                    ->orWhere('name', 'like', "%{$query}%");
            })
            ->where('is_active', true)
            ->first();

        if (! $product) {
            return response()->json(['product' => null, 'message' => 'Product not found']);
        }

        $totalStock = $product->stocks->sum('quantity');

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'price' => (float) $product->price,
                'tax_rate' => (float) $product->tax_rate,
                'image' => $product->image,
                'stock' => $totalStock,
                'alert_qty' => $product->alert_quantity,
            ],
        ]);
    }

    // ─── Product Variations CRUD ─────────────────────

    public function variations(string $slug, Product $product)
    {
        $product->load(['variations' => fn ($q) => $q->orderBy('name')]);

        return view('eshop360::catalog.products.variations', compact('product'));
    }

    public function storeVariation(Request $request, string $slug, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'values' => 'nullable|array',
            'values.*' => 'string|max:100',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products/variations', 'public');
        }

        $validated['product_id'] = $product->id;

        \Modules\Eshop360\Domain\Catalog\Models\ProductVariation::create($validated);

        return redirect()->route('eshop360.products.variations', [request()->route('slug'), $product])
            ->with('success', __('Variation created.'));
    }

    public function updateVariation(Request $request, string $slug, Product $product, \Modules\Eshop360\Domain\Catalog\Models\ProductVariation $variation): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'values' => 'nullable|array',
            'values.*' => 'string|max:100',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products/variations', 'public');
        }

        $variation->update($validated);

        return redirect()->route('eshop360.products.variations', [request()->route('slug'), $product])
            ->with('success', __('Variation updated.'));
    }

    public function destroyVariation(string $slug, Product $product, \Modules\Eshop360\Domain\Catalog\Models\ProductVariation $variation): RedirectResponse
    {
        $variation->delete();

        return redirect()->route('eshop360.products.variations', [request()->route('slug'), $product])
            ->with('success', __('Variation deleted.'));
    }

    /**
     * Bulk actions on products.
     */
    public function bulkAction(Request $request, string $slug): RedirectResponse
    {
        $validated = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:eshop_products,id',
            'action' => 'required|in:activate,deactivate,delete,change_category',
            'category_id' => 'nullable|exists:eshop_categories,id',
        ]);

        $ids = $validated['product_ids'];
        $count = count($ids);

        switch ($validated['action']) {
            case 'activate':
                Product::whereIn('id', $ids)->update(['is_active' => true]);
                $msg = __(':count produit(s) active(s).', ['count' => $count]);
                break;
            case 'deactivate':
                Product::whereIn('id', $ids)->update(['is_active' => false]);
                $msg = __(':count produit(s) desactive(s).', ['count' => $count]);
                break;
            case 'delete':
                Product::whereIn('id', $ids)->delete();
                $msg = __(':count produit(s) supprime(s).', ['count' => $count]);
                break;
            case 'change_category':
                if (empty($validated['category_id'])) {
                    return redirect()->back()->with('error', __('Veuillez selectionner une categorie.'));
                }
                Product::whereIn('id', $ids)->update(['category_id' => $validated['category_id']]);
                $msg = __(':count produit(s) deplace(s).', ['count' => $count]);
                break;
            default:
                $msg = __('Action inconnue.');
        }

        return redirect()->route('eshop360.products.index', ['slug' => $slug])->with('success', $msg);
    }
}
