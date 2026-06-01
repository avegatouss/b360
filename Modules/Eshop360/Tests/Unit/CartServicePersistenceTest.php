<?php

namespace Modules\Eshop360\Tests\Unit;

use App\Models\User;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\Sales\Models\PersistentCart;
use Modules\Eshop360\Services\CartService;
use Modules\Eshop360\Tests\TestCase;

final class CartServicePersistenceTest extends TestCase
{
    private CartService $cart;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpInstanceWithAdmin();

        $this->cart = new CartService;
    }

    private function makeProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'instance_id' => CurrentInstance::get()->id,
            'name' => 'Test Product',
            'slug' => 'test-'.uniqid(),
            'sku' => 'TST-'.uniqid(),
            'price' => 100,
            'cost_price' => 60,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'unit',
            'min_quantity' => 0,
            'alert_quantity' => 5,
            'is_active' => true,
        ], $overrides));
    }

    public function test_persist_to_db_saves_cart_for_authenticated_user(): void
    {
        $product = $this->makeProduct(['price' => 500]);

        $user = $this->makeUser('cart-test@test.com');
        $this->actingAs($user);
        $this->cart->addItem($product, 3);

        // Check DB record was created
        $record = PersistentCart::where('instance_id', CurrentInstance::get()->id)
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($record);
        $this->assertCount(1, $record->items);
        $this->assertNotNull($record->expires_at);
    }

    public function test_persist_does_nothing_for_guest(): void
    {
        // No acting as — guest context
        auth()->logout();

        $product = $this->makeProduct();
        $this->cart->addItem($product, 1);

        $this->assertSame(0, PersistentCart::count());
    }

    public function test_restore_from_db_loads_saved_cart(): void
    {
        $user = $this->makeUser('restore@test.com');
        $instance = CurrentInstance::get();

        // Simulate a previously saved cart
        PersistentCart::withoutGlobalScopes()->create([
            'instance_id' => $instance->id,
            'user_id' => $user->id,
            'items' => [
                'item_99' => [
                    'product_id' => 99,
                    'name' => 'Saved Product',
                    'sku' => 'SAVED-001',
                    'price' => 200.0,
                    'quantity' => 2,
                    'tax_rate' => 0,
                    'discount_type' => 'none',
                    'discount_value' => 0,
                    'image' => null,
                ],
            ],
            'coupon' => ['code' => 'SAVE10', 'type' => 'percentage', 'value' => 10],
            'context' => ['channel_id' => 5],
            'expires_at' => now()->addDays(3),
        ]);

        $this->actingAs($user);

        $restored = $this->cart->restoreFromDb();

        $this->assertTrue($restored);
        $this->assertCount(1, $this->cart->getCart());
        $this->assertSame('SAVE10', $this->cart->getCoupon()['code']);
        $this->assertSame(5, $this->cart->getContext()['channel_id']);
    }

    public function test_restore_skips_when_session_cart_not_empty(): void
    {
        // Create product before switching to regular user
        $product = $this->makeProduct();

        $user = $this->makeUser('skip@test.com');
        $this->actingAs($user);
        $this->cart->addItem($product, 1);

        // Save something different in DB
        PersistentCart::withoutGlobalScopes()->create([
            'instance_id' => CurrentInstance::get()->id,
            'user_id' => $user->id,
            'items' => ['item_999' => ['product_id' => 999, 'name' => 'DB Product', 'quantity' => 5, 'price' => 300]],
            'expires_at' => now()->addDays(3),
        ]);

        $restored = $this->cart->restoreFromDb();

        // Should NOT overwrite existing session cart
        $this->assertFalse($restored);
        $this->assertSame(1, $this->cart->getItemCount());
    }

    public function test_restore_skips_expired_cart(): void
    {
        $user = $this->makeUser('expired@test.com');
        $this->actingAs($user);

        PersistentCart::withoutGlobalScopes()->create([
            'instance_id' => CurrentInstance::get()->id,
            'user_id' => $user->id,
            'items' => ['item_1' => ['product_id' => 1, 'name' => 'Old', 'quantity' => 1, 'price' => 100]],
            'expires_at' => now()->subDay(),
        ]);

        $restored = $this->cart->restoreFromDb();

        $this->assertFalse($restored);
        $this->assertTrue($this->cart->isEmpty());
    }

    public function test_clear_deletes_db_record(): void
    {
        $product = $this->makeProduct();

        $user = $this->makeUser('clear@test.com');
        $this->actingAs($user);
        $this->cart->addItem($product, 2);

        // Verify DB record exists
        $this->assertSame(1, PersistentCart::where('user_id', $user->id)->count());

        $this->cart->clear();

        // DB record should be gone
        $this->assertSame(0, PersistentCart::where('user_id', $user->id)->count());
        $this->assertTrue($this->cart->isEmpty());
    }

    public function test_update_item_persists_to_db(): void
    {
        $product = $this->makeProduct();

        $user = $this->makeUser('update@test.com');
        $this->actingAs($user);
        $this->cart->addItem($product, 2);

        $key = 'item_'.$product->id;
        $this->cart->updateItem($key, 5);

        $record = PersistentCart::where('user_id', $user->id)->first();
        $this->assertSame(5, $record->items[$key]['quantity']);
    }

    public function test_calculate_totals_with_coupon(): void
    {
        $cart = [
            'item_1' => ['price' => 1000, 'quantity' => 2, 'tax_rate' => 10],
        ];
        $coupon = ['type' => 'percentage', 'value' => 15];

        $totals = $this->cart->calculateTotals($cart, $coupon);

        // subtotal = 1000 * 2 = 2000
        $this->assertSame(2000.0, $totals['subtotal']);
        // tax = 2000 * 10% = 200
        $this->assertSame(200.0, $totals['tax']);
        // discount = 2000 * 15% = 300
        $this->assertSame(300.0, $totals['discount']);
        // total = 2000 + 200 - 300 = 1900
        $this->assertSame(1900.0, $totals['total']);
    }

    public function test_calculate_totals_fixed_coupon_capped_at_subtotal(): void
    {
        $cart = [
            'item_1' => ['price' => 50, 'quantity' => 1, 'tax_rate' => 0],
        ];
        $coupon = ['type' => 'fixed', 'value' => 100];

        $totals = $this->cart->calculateTotals($cart, $coupon);

        // Discount should be capped at subtotal (50), not 100
        $this->assertSame(50.0, $totals['discount']);
        $this->assertSame(0.0, $totals['total']);
    }
}
