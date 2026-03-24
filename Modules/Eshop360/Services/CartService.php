<?php

namespace Modules\Eshop360\Services;

use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Coupon;
use Modules\Eshop360\Models\PersistentCart;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\ProductVariation;

class CartService
{
    private ?int $channelId = null;

    /**
     * Create a cart service scoped to a specific channel.
     */
    public static function forChannel(int $channelId): static
    {
        $service = new static();
        $service->channelId = $channelId;
        return $service;
    }

    public function getChannelId(): ?int
    {
        return $this->channelId;
    }

    // ─── Session Keys ────────────────────────────────

    public function cartKey(): string
    {
        $key = 'eshop_cart_instance_' . $this->instanceId();
        return $this->channelId ? $key . '_channel_' . $this->channelId : $key;
    }

    public function couponKey(): string
    {
        $key = 'eshop_cart_coupon_instance_' . $this->instanceId();
        return $this->channelId ? $key . '_channel_' . $this->channelId : $key;
    }

    public function contextKey(): string
    {
        $key = 'eshop_cart_context_instance_' . $this->instanceId();
        return $this->channelId ? $key . '_channel_' . $this->channelId : $key;
    }

    private function instanceId(): int
    {
        return CurrentInstance::idOrFail();
    }

    // ─── Cart Items ──────────────────────────────────

    /**
     * Get current cart items (with legacy key migration).
     *
     * @return array<string, array<string, mixed>>
     */
    public function getCart(): array
    {
        if (session()->has($this->cartKey())) {
            return session()->get($this->cartKey(), []);
        }

        // Migrate from legacy session key
        $legacy = session()->get('eshop_cart', []);
        if (!empty($legacy)) {
            session()->put($this->cartKey(), $legacy);
            session()->forget('eshop_cart');
        }

        return $legacy;
    }

    /**
     * Add a product (optionally with a variation) to the cart.
     */
    public function addItem(Product $product, int $quantity = 1, ?int $variationId = null): void
    {
        $cart = $this->getCart();
        $variation = null;

        if ($variationId) {
            $variation = ProductVariation::query()
                ->where('product_id', $product->id)
                ->active()
                ->find($variationId);

            if (!$variation) {
                throw new \InvalidArgumentException('Invalid variation for the selected product.');
            }
        }

        // Cart key includes variation to support multiple variants of the same product
        $key = $variation ? "item_{$product->id}_v{$variation->id}" : "item_{$product->id}";

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
        } else {
            $price = $variation ? (float) ($variation->price ?? $product->price) : (float) $product->price;
            $sku = $variation ? ($variation->sku ?: $product->sku) : $product->sku;
            $name = $variation
                ? $product->name . ' — ' . $variation->name
                : $product->name;

            $cart[$key] = [
                'product_id'     => $product->id,
                'variation_id'   => $variation?->id,
                'name'           => $name,
                'variation_name' => $variation?->name,
                'sku'            => $sku,
                'price'          => $price,
                'quantity'       => $quantity,
                'tax_rate'       => (float) $product->tax_rate,
                'discount_type'  => $product->discount_type,
                'discount_value' => (float) $product->discount_value,
                'image'          => $variation?->image ?? $product->image,
            ];
        }

        session()->put($this->cartKey(), $cart);
        $this->persistToDb();
    }

    /**
     * Update the quantity for an existing cart item.
     */
    public function updateItem(string $key, int $quantity): void
    {
        $cart = $this->getCart();

        if (!isset($cart[$key])) {
            return;
        }

        if ($quantity <= 0) {
            unset($cart[$key]);
        } else {
            $cart[$key]['quantity'] = $quantity;
        }

        session()->put($this->cartKey(), $cart);
        $this->persistToDb();
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(string $key): void
    {
        $cart = $this->getCart();
        unset($cart[$key]);
        session()->put($this->cartKey(), $cart);
        $this->persistToDb();
    }

    // ─── Coupon ──────────────────────────────────────

    /**
     * Get the currently applied coupon.
     *
     * @return array<string, mixed>|null
     */
    public function getCoupon(): ?array
    {
        if (session()->has($this->couponKey())) {
            return session()->get($this->couponKey());
        }

        // Migrate from legacy key
        $legacy = session()->get('eshop_cart_coupon');
        if ($legacy !== null) {
            session()->put($this->couponKey(), $legacy);
            session()->forget('eshop_cart_coupon');
        }

        return $legacy;
    }

    /**
     * Validate and apply a coupon code.
     *
     * @return array{success: bool, message: string, coupon?: Coupon}
     */
    public function applyCoupon(string $code): array
    {
        $coupon = Coupon::where('code', $code)
            ->visibleToChannel($this->channelId)
            ->valid()
            ->first();

        if (!$coupon) {
            return [
                'success' => false,
                'message' => __('eshop::eshop.coupon_invalid'),
            ];
        }

        session()->put($this->couponKey(), [
            'id'    => $coupon->id,
            'code'  => $coupon->code,
            'type'  => $coupon->type,
            'value' => (float) $coupon->value,
        ]);

        $this->persistToDb();

        return [
            'success' => true,
            'message' => __('eshop::eshop.coupon_applied'),
            'coupon'  => $coupon,
        ];
    }

    // ─── Cart Context (channel, pricing) ─────────────

    /**
     * Get the cart context (e.g. channel_id for pricing).
     *
     * @return array<string, mixed>|null
     */
    public function getContext(): ?array
    {
        if (session()->has($this->contextKey())) {
            return session()->get($this->contextKey());
        }

        // Migrate from legacy key
        $legacy = session()->get('eshop_cart_context');
        if ($legacy !== null) {
            session()->put($this->contextKey(), $legacy);
            session()->forget('eshop_cart_context');
        }

        return $legacy;
    }

    /**
     * Set the cart context.
     */
    public function setContext(?array $context): void
    {
        if ($context === null) {
            session()->forget($this->contextKey());
            return;
        }

        session()->put($this->contextKey(), $context);
    }

    // ─── Clear ───────────────────────────────────────

    /**
     * Clear all cart data (items, coupon, context) including legacy keys.
     */
    public function clear(): void
    {
        session()->forget([
            $this->cartKey(),
            $this->couponKey(),
            $this->contextKey(),
            // Legacy keys
            'eshop_cart',
            'eshop_cart_coupon',
            'eshop_cart_context',
        ]);

        $this->forgetDb();
    }

    // ─── Calculations ────────────────────────────────

    /**
     * Calculate all totals from the current cart state.
     *
     * @return array{subtotal: float, tax: float, discount: float, total: float}
     */
    public function calculateTotals(?array $cart = null, ?array $coupon = null): array
    {
        $cart ??= $this->getCart();
        $coupon ??= $this->getCoupon();

        $subtotal = 0.0;
        $tax = 0.0;

        foreach ($cart as $item) {
            $unitPrice = (float) ($item['unit_price'] ?? $item['price'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);
            $lineTotal = (float) ($item['total'] ?? ($unitPrice * $quantity));
            $subtotal += $lineTotal;
            $tax += round($lineTotal * (((float) ($item['tax_rate'] ?? 0)) / 100), 2);
        }

        $discount = 0.0;
        if ($coupon) {
            if (($coupon['type'] ?? null) === 'percentage') {
                $discount = round($subtotal * (((float) ($coupon['value'] ?? 0)) / 100), 2);
            } else {
                $discount = min((float) ($coupon['value'] ?? 0), $subtotal);
            }
        }

        return [
            'subtotal' => round($subtotal, 2),
            'tax'      => round($tax, 2),
            'discount' => round($discount, 2),
            'total'    => round(max(0, $subtotal + $tax - $discount), 2),
        ];
    }

    /**
     * Get the subtotal (sum of item prices * quantity before tax/discount).
     */
    public function getSubtotal(): float
    {
        return $this->calculateTotals()['subtotal'];
    }

    /**
     * Get total tax across all cart items.
     */
    public function getTax(): float
    {
        return $this->calculateTotals()['tax'];
    }

    /**
     * Get total discount (per-item + coupon).
     */
    public function getDiscount(): float
    {
        return $this->calculateTotals()['discount'];
    }

    /**
     * Get the final total (subtotal + tax - discount).
     */
    public function getTotal(): float
    {
        return $this->calculateTotals()['total'];
    }

    /**
     * Get the total number of items in the cart.
     */
    public function getItemCount(): int
    {
        $count = 0;

        foreach ($this->getCart() as $item) {
            $count += (int) ($item['quantity'] ?? 0);
        }

        return $count;
    }

    /**
     * Check if the cart is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->getCart());
    }

    // ─── DB Persistence (write-through) ──────────────

    /**
     * Persist the current session cart to database for the authenticated user.
     * Called automatically after cart mutations when user is authenticated.
     */
    public function persistToDb(): void
    {
        $userId = auth()->id();
        if (!$userId) {
            return;
        }

        $instanceId = $this->instanceId();

        $where = ['instance_id' => $instanceId, 'user_id' => $userId];
        if ($this->channelId) {
            $where['channel_id'] = $this->channelId;
        }

        PersistentCart::updateOrCreate(
            $where,
            [
                'items' => $this->getCart(),
                'coupon' => $this->getCoupon(),
                'context' => $this->getContext(),
                'channel_id' => $this->channelId,
                'expires_at' => now()->addDays(7),
            ],
        );
    }

    /**
     * Restore cart from database into session (e.g., on login or device switch).
     * Only restores if the session cart is empty.
     */
    public function restoreFromDb(): bool
    {
        $userId = auth()->id();
        if (!$userId) {
            return false;
        }

        // Don't overwrite an existing session cart
        if (!empty($this->getCart())) {
            return false;
        }

        $instanceId = $this->instanceId();

        $query = PersistentCart::where('instance_id', $instanceId)
            ->where('user_id', $userId)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });

        if ($this->channelId) {
            $query->where('channel_id', $this->channelId);
        } else {
            $query->whereNull('channel_id');
        }

        $saved = $query->first();

        if (!$saved || empty($saved->items)) {
            return false;
        }

        session()->put($this->cartKey(), $saved->items);

        if ($saved->coupon) {
            session()->put($this->couponKey(), $saved->coupon);
        }

        if ($saved->context) {
            session()->put($this->contextKey(), $saved->context);
        }

        return true;
    }

    /**
     * Delete the persistent cart from database.
     */
    public function forgetDb(): void
    {
        $userId = auth()->id();
        if (!$userId) {
            return;
        }

        $query = PersistentCart::where('instance_id', $this->instanceId())
            ->where('user_id', $userId);

        if ($this->channelId) {
            $query->where('channel_id', $this->channelId);
        } else {
            $query->whereNull('channel_id');
        }

        $query->delete();
    }
}
