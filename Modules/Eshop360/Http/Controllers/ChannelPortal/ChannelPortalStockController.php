<?php

namespace Modules\Eshop360\Http\Controllers\ChannelPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Models\Stock;

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
}
