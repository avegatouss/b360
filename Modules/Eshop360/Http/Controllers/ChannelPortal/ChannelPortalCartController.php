<?php

namespace Modules\Eshop360\Http\Controllers\ChannelPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Services\CartService;

class ChannelPortalCartController extends Controller
{
    public function add(Request $request)
    {
        $channel = $request->resolved_channel;
        $cart = CartService::forChannel($channel->id);

        $validated = $request->validate([
            'product_id' => 'required|integer|exists:eshop_products,id',
            'quantity' => 'integer|min:1',
            'variation_id' => 'nullable|integer',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Use channel pricing
        $channelPrice = $channel->productPrices()->where('product_id', $product->id)->first();
        if ($channelPrice) {
            $product->price = $channelPrice->sale_price;
        }

        $cart->addItem($product, $validated['quantity'] ?? 1, $validated['variation_id'] ?? null);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'count' => $cart->getItemCount()]);
        }

        return back()->with('success', 'Produit ajouté au panier.');
    }

    public function update(Request $request, $channelParam, $itemKey)
    {
        $channel = $request->resolved_channel;
        $cart = CartService::forChannel($channel->id);

        $validated = $request->validate(['quantity' => 'required|integer|min:0']);
        $cart->updateItem($itemKey, $validated['quantity']);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'totals' => $cart->calculateTotals()]);
        }

        return back();
    }

    public function remove(Request $request, $channelParam, $itemKey)
    {
        $channel = $request->resolved_channel;
        $cart = CartService::forChannel($channel->id);
        $cart->removeItem($itemKey);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    public function clear(Request $request)
    {
        $channel = $request->resolved_channel;
        CartService::forChannel($channel->id)->clear();

        return back()->with('success', 'Panier vidé.');
    }
}
