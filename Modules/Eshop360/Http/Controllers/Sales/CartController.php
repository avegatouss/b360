<?php

namespace Modules\Eshop360\Http\Controllers\Sales;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\Promotions\Models\Coupon;
use Modules\Eshop360\Services\ProductPricingService;

class CartController extends Controller
{
    public function index(string $slug): JsonResponse
    {
        $cart = $this->getCart();
        $totals = $this->calculateTotals($cart);

        return response()->json([
            'items' => array_values($cart),
            'count' => count($cart),
            'subtotal' => $totals['subtotal'],
            'tax' => $totals['tax'],
            'discount' => $totals['discount'],
            'total' => $totals['total'],
            'coupon' => $this->getCoupon(),
            'context' => $this->getCartContext(),
        ]);
    }

    public function add(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:eshop_products,id',
            'variation_id' => 'nullable|exists:eshop_product_variations,id',
            'quantity' => 'nullable|integer|min:1',
            'channel_id' => 'nullable|exists:eshop_distribution_channels,id',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $variation = null;
        if (isset($validated['variation_id'])) {
            $variation = \Modules\Eshop360\Domain\Catalog\Models\ProductVariation::query()
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->find($validated['variation_id']);

            if (! $variation) {
                return $this->respond($request, [
                    'message' => __('Invalid variation for the selected product.'),
                ], __('Invalid variation for the selected product.'), 422, 'error');
            }
        }
        $quantity = $validated['quantity'] ?? 1;
        $cart = $this->getCart();
        $requestedContext = $this->normalizeContext([
            'channel_id' => $validated['channel_id'] ?? null,
        ]);
        $cartContext = $this->resolveContextForMutation($requestedContext, $this->getCartContext(), ! empty($cart));

        if ($cartContext === false) {
            return $this->respond(
                $request,
                ['message' => __('This cart already uses another pricing context. Clear it first.')],
                __('This cart already uses another pricing context. Clear it first.'),
                422,
                'error'
            );
        }

        $pricing = app(ProductPricingService::class)->resolve(
            $product,
            $cartContext['channel_id'] ?? null,
            true
        );

        $key = $variation ? "{$product->id}_v{$variation->id}" : (string) $product->id;

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
            $lineDiscount = (float) ($cart[$key]['line_discount'] ?? 0);
            $gross = $cart[$key]['quantity'] * $cart[$key]['unit_price'];
            $cart[$key]['total'] = round($gross * (1 - $lineDiscount / 100), 2);
        } else {
            $unitPrice = ($variation && $variation->price !== null)
                ? (float) $variation->price
                : $pricing['unit_price'];

            $cart[$key] = [
                'product_id' => $product->id,
                'variation_id' => $variation?->id,
                'name' => $variation ? $product->name.' — '.$variation->name : $product->name,
                'variation_name' => $variation?->name,
                'sku' => $variation?->sku ?: $product->sku,
                'image' => $variation?->image ?? $product->image,
                'unit_price' => $unitPrice,
                'original_price' => $pricing['original_price'],
                'tax_rate' => (float) $product->tax_rate,
                'quantity' => $quantity,
                'total' => $unitPrice * $quantity,
                'channel_id' => $pricing['channel_id'],
                'price_source' => $variation ? 'variation' : $pricing['price_source'],
                'line_discount' => 0,
            ];
        }

        $this->storeCart($cart);
        $this->storeCartContext($cartContext);
        $totals = $this->calculateTotals($cart);

        return $this->respond($request, [
            'message' => __('Product added to cart.'),
            'items' => array_values($cart),
            'count' => count($cart),
            'subtotal' => $totals['subtotal'],
            'tax' => $totals['tax'],
            'discount' => $totals['discount'],
            'total' => $totals['total'],
            'context' => $this->getCartContext(),
        ], __('Product added to cart.'));
    }

    public function update(Request $request, string $slug, string $itemKey): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'line_discount' => 'nullable|numeric|min:0|max:100',
        ]);

        $cart = $this->getCart();
        $key = $this->resolveItemKey($itemKey, $request);

        if (! isset($cart[$key])) {
            return $this->respond(
                $request,
                ['message' => __('Product not found in cart.')],
                __('Product not found in cart.'),
                404,
                'error'
            );
        }

        $cart[$key]['quantity'] = $validated['quantity'];

        if (array_key_exists('line_discount', $validated) && $validated['line_discount'] !== null) {
            $cart[$key]['line_discount'] = (float) $validated['line_discount'];
        }

        $lineDiscount = (float) ($cart[$key]['line_discount'] ?? 0);
        $gross = $cart[$key]['quantity'] * $cart[$key]['unit_price'];
        $cart[$key]['total'] = round($gross * (1 - $lineDiscount / 100), 2);

        $this->storeCart($cart);
        $totals = $this->calculateTotals($cart);

        return $this->respond($request, [
            'message' => __('Cart updated.'),
            'items' => array_values($cart),
            'count' => count($cart),
            'subtotal' => $totals['subtotal'],
            'tax' => $totals['tax'],
            'discount' => $totals['discount'],
            'total' => $totals['total'],
            'context' => $this->getCartContext(),
        ], __('Cart updated.'));
    }

    public function remove(Request $request, string $slug, string $itemKey): JsonResponse|RedirectResponse
    {
        $cart = $this->getCart();
        $key = $this->resolveItemKey($itemKey, $request);

        unset($cart[$key]);

        $this->storeCart($cart);
        $totals = $this->calculateTotals($cart);

        return $this->respond($request, [
            'message' => __('Product removed from cart.'),
            'items' => array_values($cart),
            'count' => count($cart),
            'subtotal' => $totals['subtotal'],
            'tax' => $totals['tax'],
            'discount' => $totals['discount'],
            'total' => $totals['total'],
            'context' => $this->getCartContext(),
        ], __('Product removed from cart.'));
    }

    public function clear(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $this->clearCart();

        return $this->respond($request, [
            'message' => __('Cart cleared.'),
            'items' => [],
            'count' => 0,
            'subtotal' => 0,
            'tax' => 0,
            'discount' => 0,
            'total' => 0,
            'context' => null,
        ], __('Cart cleared.'));
    }

    public function applyCoupon(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $cartContext = $this->getCartContext();
        $channelId = $cartContext['channel_id'] ?? null;

        $coupon = Coupon::where('code', $validated['code'])
            ->where('instance_id', CurrentInstance::idOrFail())
            ->visibleToChannel($channelId)
            ->valid()
            ->first();

        if (! $coupon) {
            return $this->respond(
                $request,
                ['message' => __('Invalid or expired coupon code.')],
                __('Invalid or expired coupon code.'),
                422,
                'error'
            );
        }

        $cart = $this->getCart();

        $this->storeCoupon([
            'id' => $coupon->id,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => (float) $coupon->value,
        ]);

        $totals = $this->calculateTotals($cart);

        return $this->respond($request, [
            'message' => __('Coupon applied successfully.'),
            'coupon' => $this->getCoupon(),
            'subtotal' => $totals['subtotal'],
            'tax' => $totals['tax'],
            'discount' => $totals['discount'],
            'total' => $totals['total'],
            'context' => $this->getCartContext(),
        ], __('Coupon applied successfully.'));
    }

    private function calculateTotals(array $cart): array
    {
        $subtotal = 0;
        $tax = 0;

        foreach ($cart as $item) {
            $subtotal += $item['total'];
            $tax += round($item['total'] * ($item['tax_rate'] / 100), 2);
        }

        $discount = 0;
        $coupon = $this->getCoupon();
        if ($coupon) {
            if ($coupon['type'] === 'percentage') {
                $discount = round($subtotal * ($coupon['value'] / 100), 2);
            } else {
                $discount = min($coupon['value'], $subtotal);
            }
        }

        return [
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'discount' => round($discount, 2),
            'total' => round($subtotal + $tax - $discount, 2),
        ];
    }

    private function resolveItemKey(string $itemKey, Request $request): string
    {
        $productId = $request->input('product_id');
        $variationId = $request->input('variation_id');

        if ($productId !== null && $productId !== '') {
            return $variationId ? "{$productId}_v{$variationId}" : (string) $productId;
        }

        return $itemKey;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function respond(
        Request $request,
        array $payload,
        string $message,
        int $status = 200,
        string $flashType = 'success',
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($payload, $status);
        }

        return redirect()->back()->with($flashType, $message);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getCart(): array
    {
        if (session()->has($this->scopedCartKey())) {
            return session()->get($this->scopedCartKey(), []);
        }

        $legacyCart = session()->get('eshop_cart', []);

        if (! empty($legacyCart)) {
            session()->put($this->scopedCartKey(), $legacyCart);
        }

        return $legacyCart;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getCoupon(): ?array
    {
        if (session()->has($this->scopedCouponKey())) {
            return session()->get($this->scopedCouponKey());
        }

        $legacyCoupon = session()->get('eshop_cart_coupon');

        if ($legacyCoupon !== null) {
            session()->put($this->scopedCouponKey(), $legacyCoupon);
        }

        return $legacyCoupon;
    }

    /**
     * @return array{channel_id: int|null}|null
     */
    private function getCartContext(): ?array
    {
        if (session()->has($this->scopedCartContextKey())) {
            return $this->normalizeContext(session()->get($this->scopedCartContextKey()));
        }

        $legacyContext = session()->get('eshop_cart_context');

        if ($legacyContext !== null) {
            $context = $this->normalizeContext($legacyContext);
            $this->storeCartContext($context);

            return $context;
        }

        $firstItem = collect($this->getCart())->first();

        if (is_array($firstItem)) {
            $context = $this->normalizeContext([
                'channel_id' => $firstItem['channel_id'] ?? null,
            ]);

            if ($context !== null) {
                $this->storeCartContext($context);
            }

            return $context;
        }

        return null;
    }

    /**
     * @param  array<string, array<string, mixed>>  $cart
     */
    private function storeCart(array $cart): void
    {
        session()->put($this->scopedCartKey(), $cart);
        session()->put('eshop_cart', $cart);

        if (empty($cart)) {
            $this->storeCartContext(null);
        }
    }

    /**
     * @param  array<string, mixed>  $coupon
     */
    private function storeCoupon(array $coupon): void
    {
        session()->put($this->scopedCouponKey(), $coupon);
        session()->put('eshop_cart_coupon', $coupon);
    }

    /**
     * @param  array{channel_id: int|null}|null  $context
     */
    private function storeCartContext(?array $context): void
    {
        if ($context === null) {
            session()->forget([$this->scopedCartContextKey(), 'eshop_cart_context']);

            return;
        }

        session()->put($this->scopedCartContextKey(), $context);
        session()->put('eshop_cart_context', $context);
    }

    private function clearCart(): void
    {
        session()->forget($this->scopedCartKey());
        session()->forget($this->scopedCouponKey());
        session()->forget($this->scopedCartContextKey());
        session()->forget('eshop_cart');
        session()->forget('eshop_cart_coupon');
        session()->forget('eshop_cart_context');
    }

    private function scopedCartKey(): string
    {
        $instanceId = CurrentInstance::idOrFail();

        return 'eshop_cart_instance_'.$instanceId;
    }

    private function scopedCouponKey(): string
    {
        $instanceId = CurrentInstance::idOrFail();

        return 'eshop_cart_coupon_instance_'.$instanceId;
    }

    private function scopedCartContextKey(): string
    {
        $instanceId = CurrentInstance::idOrFail();

        return 'eshop_cart_context_instance_'.$instanceId;
    }

    /**
     * @param  array<string, mixed>|null  $context
     * @return array{channel_id: int|null}|null
     */
    private function normalizeContext(?array $context): ?array
    {
        $channelId = isset($context['channel_id']) && $context['channel_id'] !== ''
            ? (int) $context['channel_id']
            : null;

        if ($channelId !== null) {
            return [
                'channel_id' => $channelId,
            ];
        }

        return null;
    }

    /**
     * @param  array{channel_id: int|null}|null  $requestedContext
     * @param  array{channel_id: int|null}|null  $existingContext
     * @return array{channel_id: int|null}|null|false
     */
    private function resolveContextForMutation(?array $requestedContext, ?array $existingContext, bool $cartHasItems): array|null|false
    {
        if (! $cartHasItems) {
            return $requestedContext;
        }

        if ($requestedContext === null) {
            return $existingContext;
        }

        if ($existingContext === null) {
            return $requestedContext;
        }

        if ($requestedContext === $existingContext) {
            return $existingContext;
        }

        return false;
    }
}
