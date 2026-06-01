<?php

namespace Modules\Eshop360\Http\Controllers\ChannelPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Domain\Catalog\Models\Product;
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
            'variation_id' => 'nullable|integer|exists:eshop_product_variations,id',
        ]);

        // Product must belong to this channel's catalog
        $product = $channel->products()
            ->where('eshop_products.id', $validated['product_id'])
            ->firstOrFail();

        // Apply channel pricing
        $channelPrice = $channel->productPrices()->where('product_id', $product->id)->first();
        if ($channelPrice) {
            $product->price = $channelPrice->sale_price;
        }

        try {
            $cart->addItem($product, $validated['quantity'] ?? 1, $validated['variation_id'] ?? null);
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

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
