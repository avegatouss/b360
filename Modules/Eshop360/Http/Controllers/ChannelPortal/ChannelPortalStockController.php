<?php

namespace Modules\Eshop360\Http\Controllers\ChannelPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\StockAdjustment;
use Modules\Eshop360\Services\StockService;

class ChannelPortalStockController extends Controller
{
    /**
     * List products with stock levels for the channel's warehouse (read-only).
     */
    public function index(Request $request)
    {
        $channel = $request->resolved_channel;

        $query = $channel->products()
            ->with('category', 'brand');

        // If the channel has a warehouse, load stock for that warehouse
        $warehouseId = $channel->warehouse_id;

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('eshop_products.name', 'like', "%{$search}%")
                  ->orWhere('eshop_products.sku', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('eshop_products.name')->paginate(20)->withQueryString();

        // Load stock for each product in the channel's warehouse
        $productIds = $products->pluck('id')->toArray();
        $stocks = [];
        if ($warehouseId && count($productIds) > 0) {
            $stocks = Stock::where('warehouse_id', $warehouseId)
                ->whereIn('product_id', $productIds)
                ->pluck('quantity', 'product_id')
                ->toArray();
        }

        return view('eshop360::channel-portal.stock.index', compact('channel', 'products', 'stocks', 'warehouseId'));
    }

    /**
     * Show the stock adjustments form for the channel's warehouse.
     */
    public function adjustments(Request $request)
    {
        $channel = $request->resolved_channel;
        $warehouseId = $channel->warehouse_id;

        if (! $warehouseId) {
            return back()->with('error', 'Aucun entrepôt associé à ce canal.');
        }

        $products = $channel->products()
            ->orderBy('eshop_products.name')
            ->get();

        // Load current stock levels
        $productIds = $products->pluck('id')->toArray();
        $stocks = Stock::where('warehouse_id', $warehouseId)
            ->whereIn('product_id', $productIds)
            ->pluck('quantity', 'product_id')
            ->toArray();

        // Recent adjustments
        $recentAdjustments = StockAdjustment::where('warehouse_id', $warehouseId)
            ->whereIn('product_id', $productIds)
            ->with('product')
            ->latest()
            ->limit(20)
            ->get();

        return view('eshop360::channel-portal.stock.adjustments', compact(
            'channel', 'products', 'stocks', 'warehouseId', 'recentAdjustments'
        ));
    }

    /**
     * Process a stock adjustment for a product in the channel's warehouse.
     */
    public function storeAdjustment(Request $request)
    {
        $channel = $request->resolved_channel;
        $warehouseId = $channel->warehouse_id;

        if (! $warehouseId) {
            return back()->with('error', 'Aucun entrepôt associé à ce canal.');
        }

        $validated = $request->validate([
            'product_id' => 'required|integer|exists:eshop_products,id',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:out',
            'reason' => 'nullable|string|max:500',
        ]);

        // Verify product belongs to channel
        $product = $channel->products()->where('eshop_products.id', $validated['product_id'])->firstOrFail();

        $stockService = app(StockService::class);

        $stockService->adjustStock(
            $product,
            $warehouseId,
            $validated['quantity'],
            $validated['type'],
            $validated['reason'] ?? 'Ajustement canal',
            auth()->id(),
        );

        return back()->with('success', 'Ajustement de stock effectué.');
    }
}
