<?php

namespace Modules\Eshop360\Tests\Unit;

use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Coupon;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Services\CartService;
use Modules\Eshop360\Tests\TestCase;

final class CartServiceTest extends TestCase
{
    private CartService $cart;

    protected function setUp(): void
    {
        parent::setUp();

        $instance = $this->makeRootInstance();
        CurrentInstance::set($instance);

        $this->cart = new CartService();
    }

    private function makeProduct(array $overrides = []): Product
    {
        $instance = CurrentInstance::get();

        return Product::create(array_merge([
            'instance_id' => $instance->id,
            'name' => 'Test Product',
            'slug' => 'test-product-' . uniqid(),
            'sku' => 'TST-' . uniqid(),
            'price' => 100,
            'cost_price' => 60,
            'tax_rate' => 18,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'unit',
            'min_quantity' => 0,
            'alert_quantity' => 5,
            'is_active' => true,
        ], $overrides));
    }

    public function test_add_item_to_cart(): void
    {
        $product = $this->makeProduct(['price' => 250, 'tax_rate' => 0]);

        $this->cart->addItem($product, 2);

        $cart = $this->cart->getCart();
        $key = 'item_' . $product->id;

        $this->assertArrayHasKey($key, $cart);
        $this->assertSame($product->id, $cart[$key]['product_id']);
        $this->assertSame(2, $cart[$key]['quantity']);
        $this->assertSame(250.0, $cart[$key]['price']);
    }

    public function test_add_same_item_increases_quantity(): void
    {
        $product = $this->makeProduct();

        $this->cart->addItem($product, 2);
        $this->cart->addItem($product, 3);

        $cart = $this->cart->getCart();
        $key = 'item_' . $product->id;

        $this->assertSame(5, $cart[$key]['quantity']);
    }

    public function test_update_cart_quantity(): void
    {
        $product = $this->makeProduct();
        $this->cart->addItem($product, 2);

        $key = 'item_' . $product->id;
        $this->cart->updateItem($key, 7);

        $cart = $this->cart->getCart();
        $this->assertSame(7, $cart[$key]['quantity']);
    }

    public function test_update_to_zero_removes_item(): void
    {
        $product = $this->makeProduct();
        $this->cart->addItem($product, 2);

        $key = 'item_' . $product->id;
        $this->cart->updateItem($key, 0);

        $cart = $this->cart->getCart();
        $this->assertArrayNotHasKey($key, $cart);
    }

    public function test_remove_item_from_cart(): void
    {
        $product = $this->makeProduct();
        $this->cart->addItem($product, 5);

        $key = 'item_' . $product->id;
        $this->cart->removeItem($key);

        $cart = $this->cart->getCart();
        $this->assertEmpty($cart);
    }

    public function test_apply_coupon_reduces_total(): void
    {
        $instance = CurrentInstance::get();

        $product = $this->makeProduct(['price' => 1000, 'tax_rate' => 0, 'discount_type' => 'none', 'discount_value' => 0]);
        $this->cart->addItem($product, 1);

        $coupon = Coupon::create([
            'instance_id' => $instance->id,
            'name' => 'Promo 10%',
            'code' => 'PROMO10',
            'type' => 'percentage',
            'value' => 10,
            'usage_limit' => 100,
            'used_count' => 0,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
            'is_active' => true,
        ]);

        $result = $this->cart->applyCoupon('PROMO10');

        $this->assertTrue($result['success']);

        // subtotal = 1000, coupon discount = 10% of 1000 = 100
        $discount = $this->cart->getDiscount();
        $this->assertSame(100.0, $discount);

        // total = subtotal + tax - discount = 1000 + 0 - 100 = 900
        $total = $this->cart->getTotal();
        $this->assertSame(900.0, $total);
    }

    public function test_apply_invalid_coupon_fails(): void
    {
        $result = $this->cart->applyCoupon('NONEXISTENT');

        $this->assertFalse($result['success']);
    }

    public function test_cart_total_calculation_with_tax(): void
    {
        // price=100, tax_rate=18%, qty=2
        $product = $this->makeProduct(['price' => 100, 'tax_rate' => 18, 'discount_type' => 'none', 'discount_value' => 0]);
        $this->cart->addItem($product, 2);

        $subtotal = $this->cart->getSubtotal();
        $tax = $this->cart->getTax();
        $total = $this->cart->getTotal();

        // subtotal = 100 * 2 = 200
        $this->assertSame(200.0, $subtotal);
        // tax = 200 * 18/100 = 36
        $this->assertSame(36.0, $tax);
        // total = 200 + 36 = 236
        $this->assertSame(236.0, $total);
    }

    public function test_item_count(): void
    {
        $product1 = $this->makeProduct();
        $product2 = $this->makeProduct();

        $this->cart->addItem($product1, 3);
        $this->cart->addItem($product2, 2);

        $this->assertSame(5, $this->cart->getItemCount());
    }

    public function test_clear_empties_cart(): void
    {
        $product = $this->makeProduct();
        $this->cart->addItem($product, 5);

        $this->cart->clear();

        $this->assertEmpty($this->cart->getCart());
        $this->assertSame(0, $this->cart->getItemCount());
    }
}
