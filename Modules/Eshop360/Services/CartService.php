<?php

namespace Modules\Eshop360\Services;

use Illuminate\Support\Str;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Coupon;
use Modules\Eshop360\Models\Product;

class CartService
{
    /**
     * Session key for the cart, scoped per instance.
     */
    protected function sessionKey(): string
    {
        $instance = CurrentInstance::get();
        $instanceId = $instance ? $instance->id : 0;

        return "eshop360.cart.{$instanceId}";
    }

    /**
     * Session key for the applied coupon.
     */
    protected function couponKey(): string
    {
        $instance = CurrentInstance::get();
        $instanceId = $instance ? $instance->id : 0;

        return "eshop360.coupon.{$instanceId}";
    }

    /**
     * Get current cart items from session.
     *
     * @return array<string, array{product_id: int, name: string, sku: string, price: float, quantity: int, tax_rate: float, discount_type: string|null, discount_value: float, image: string|null}>
     */
    public function getCart(): array
    {
        return session()->get($this->sessionKey(), []);
    }

    /**
     * Add a product to the cart.
     */
    public function addItem(Product $product, int $quantity = 1): void
    {
        $cart = $this->getCart();
        $key = 'item_' . $product->id;

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
        } else {
            $cart[$key] = [
                'product_id'     => $product->id,
                'name'           => $product->name,
                'sku'            => $product->sku,
                'price'          => (float) $product->price,
                'quantity'       => $quantity,
                'tax_rate'       => (float) $product->tax_rate,
                'discount_type'  => $product->discount_type,
                'discount_value' => (float) $product->discount_value,
                'image'          => $product->image,
            ];
        }

        session()->put($this->sessionKey(), $cart);
    }

    /**
     * Update the quantity for an existing cart item.
     */
    public function updateItem(string $key, int $quantity): void
    {
        $cart = $this->getCart();

        if (! isset($cart[$key])) {
            return;
        }

        if ($quantity <= 0) {
            unset($cart[$key]);
        } else {
            $cart[$key]['quantity'] = $quantity;
        }

        session()->put($this->sessionKey(), $cart);
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(string $key): void
    {
        $cart = $this->getCart();
        unset($cart[$key]);
        session()->put($this->sessionKey(), $cart);
    }

    /**
     * Clear all items and coupon from the cart.
     */
    public function clear(): void
    {
        session()->forget($this->sessionKey());
        session()->forget($this->couponKey());
    }

    /**
     * Validate and apply a coupon code.
     *
     * @return array{success: bool, message: string, coupon?: Coupon}
     */
    public function applyCoupon(string $code): array
    {
        $coupon = Coupon::where('code', $code)->valid()->first();

        if (! $coupon) {
            return [
                'success' => false,
                'message' => 'Coupon invalide ou expiré.',
            ];
        }

        session()->put($this->couponKey(), [
            'id'    => $coupon->id,
            'code'  => $coupon->code,
            'type'  => $coupon->type,
            'value' => (float) $coupon->value,
        ]);

        return [
            'success' => true,
            'message' => 'Coupon appliqué avec succès.',
            'coupon'  => $coupon,
        ];
    }

    /**
     * Calculate the subtotal (sum of item prices * quantity before tax/discount).
     */
    public function getSubtotal(): float
    {
        $subtotal = 0.0;

        foreach ($this->getCart() as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        return round($subtotal, 2);
    }

    /**
     * Calculate total tax across all cart items.
     */
    public function getTax(): float
    {
        $tax = 0.0;

        foreach ($this->getCart() as $item) {
            $lineTotal = $item['price'] * $item['quantity'];
            $tax += $lineTotal * ($item['tax_rate'] / 100);
        }

        return round($tax, 2);
    }

    /**
     * Calculate total discount (per-item product discounts + coupon).
     */
    public function getDiscount(): float
    {
        $discount = 0.0;

        // Per-item product discounts
        foreach ($this->getCart() as $item) {
            if (! empty($item['discount_type']) && $item['discount_value'] > 0) {
                $lineTotal = $item['price'] * $item['quantity'];

                if ($item['discount_type'] === 'percentage') {
                    $discount += $lineTotal * ($item['discount_value'] / 100);
                } else {
                    // Fixed discount per unit
                    $discount += $item['discount_value'] * $item['quantity'];
                }
            }
        }

        // Coupon discount
        $coupon = session()->get($this->couponKey());

        if ($coupon) {
            $subtotal = $this->getSubtotal();

            if ($coupon['type'] === 'percentage') {
                $discount += $subtotal * ($coupon['value'] / 100);
            } else {
                $discount += $coupon['value'];
            }
        }

        return round($discount, 2);
    }

    /**
     * Calculate the final total (subtotal + tax - discount).
     */
    public function getTotal(): float
    {
        $total = $this->getSubtotal() + $this->getTax() - $this->getDiscount();

        return round(max(0, $total), 2);
    }

    /**
     * Get the total number of items in the cart.
     */
    public function getItemCount(): int
    {
        $count = 0;

        foreach ($this->getCart() as $item) {
            $count += $item['quantity'];
        }

        return $count;
    }
}
