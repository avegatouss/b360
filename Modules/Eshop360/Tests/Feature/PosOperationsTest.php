<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\Inventory\Models\Stock;
use Modules\Eshop360\Domain\Inventory\Models\Store;
use Modules\Eshop360\Domain\Inventory\Models\Warehouse;
use Modules\Eshop360\Domain\Promotions\Models\Coupon;
use Modules\Eshop360\Domain\Sales\Models\CashRegister;
use Modules\Eshop360\Domain\Sales\Models\Holding;
use Modules\Eshop360\Domain\Sales\Models\Order;
use Modules\Eshop360\Tests\TestCase;

final class PosOperationsTest extends TestCase
{
    public function test_open_sale_receipt_and_close_register_flow_is_operational(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);

        $warehouse = Warehouse::create([
            'instance_id' => $instance->id,
            'name' => 'Depot Caisse',
            'code' => 'DEP-CAISSE',
            'is_active' => true,
        ]);

        $store = Store::create([
            'instance_id' => $instance->id,
            'warehouse_id' => $warehouse->id,
            'name' => 'Pharmacie Centrale',
            'code' => 'STORE-CENTRAL',
            'is_active' => true,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Sirop toux',
            'slug' => 'sirop-toux',
            'sku' => 'SIR-TX',
            'price' => 10,
            'cost_price' => 6,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'unit',
            'min_quantity' => 0,
            'alert_quantity' => 2,
            'is_active' => true,
        ]);

        Stock::create([
            'instance_id' => $instance->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
        ]);

        $this->actingAs($user)
            ->post(route('eshop360.pos.registers.open', ['slug' => $instance->slug]), [
                'store_id' => $store->id,
                'opening_amount' => 50,
                'notes' => 'Ouverture matin',
            ])
            ->assertRedirect(route('eshop360.pos.index', ['slug' => $instance->slug]));

        $register = CashRegister::firstOrFail();

        $this->assertSame('open', $register->status);
        $this->assertSame($store->id, $register->store_id);

        $this->actingAs($user)
            ->post(route('eshop360.sales.store', ['slug' => $instance->slug]), [
                'payment_method' => 'cash',
                'paid_amount' => 20,
                'source' => 'pos',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                    ],
                ],
            ])
            ->assertStatus(302);

        $order = Order::with('cashRegister')->firstOrFail();

        $this->assertSame($register->id, $order->cash_register_id);
        $this->assertSame($store->id, $order->store_id);
        $this->assertSame($warehouse->id, $order->warehouse_id);

        $this->actingAs($user)
            ->get(route('eshop360.orders.receipt', ['slug' => $instance->slug, 'order' => $order]))
            ->assertOk()
            ->assertSee('Reçu', false)
            ->assertSee($order->order_number, false);

        $this->actingAs($user)
            ->post(route('eshop360.pos.registers.close', ['slug' => $instance->slug, 'register' => $register]), [
                'closing_amount' => 70,
                'notes' => 'Cloture soir',
            ])
            ->assertRedirect(route('eshop360.pos.index', ['slug' => $instance->slug]));

        $register->refresh();

        $this->assertSame('closed', $register->status);
        $this->assertSame(70.0, (float) $register->closing_amount);
        $this->assertSame(70.0, (float) $register->expected_amount);
        $this->assertSame(0.0, (float) $register->difference);
    }

    public function test_hold_and_resume_restore_cart_and_coupon_snapshot(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);

        $coupon = Coupon::create([
            'instance_id' => $instance->id,
            'name' => 'Coupon attente',
            'code' => 'HOLD10',
            'type' => 'fixed',
            'value' => 10,
            'usage_limit' => 10,
            'used_count' => 0,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
            'is_active' => true,
        ]);

        $cart = [
            '42' => [
                'product_id' => 42,
                'name' => 'Produit attente',
                'sku' => 'HOLD-42',
                'unit_price' => 15,
                'original_price' => 18,
                'tax_rate' => 0,
                'quantity' => 2,
                'total' => 30,
            ],
        ];

        $this->actingAs($user)
            ->withSession([
                'eshop_cart' => $cart,
                'eshop_cart_context' => [
                    'channel_id' => 12,
                ],
                'eshop_cart_coupon' => [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'type' => 'fixed',
                    'value' => 10,
                ],
                'eshop_cart_instance_'.$instance->id => $cart,
                'eshop_cart_context_instance_'.$instance->id => [
                    'channel_id' => 12,
                ],
                'eshop_cart_coupon_instance_'.$instance->id => [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'type' => 'fixed',
                    'value' => 10,
                ],
            ])
            ->post(route('eshop360.pos.holdings.store', ['slug' => $instance->slug]), [
                'notes' => 'Patient absent',
            ])
            ->assertRedirect(route('eshop360.pos.index', ['slug' => $instance->slug]));

        $holding = Holding::firstOrFail();

        $this->assertSame('Patient absent', $holding->notes);
        $this->assertSame(30.0, (float) $holding->subtotal);
        $this->assertSame(10.0, (float) $holding->discount_amount);
        $this->assertSame($coupon->code, $holding->held_coupon['code'] ?? null);
        $this->assertFalse(session()->has('eshop_cart'));

        $this->actingAs($user)
            ->post(route('eshop360.pos.holdings.resume', ['slug' => $instance->slug, 'holding' => $holding]))
            ->assertRedirect(route('eshop360.pos.index', ['slug' => $instance->slug]));

        $this->assertDatabaseMissing('eshop_holdings', ['id' => $holding->id]);
        $this->assertSame('Produit attente', session('eshop_cart.42.name'));
        $this->assertSame($coupon->code, session('eshop_cart_coupon.code'));
        $this->assertSame(12, session('eshop_cart_context.channel_id'));
        $this->assertSame('Produit attente', session('eshop_cart_instance_'.$instance->id.'.42.name'));
        $this->assertSame($coupon->code, session('eshop_cart_coupon_instance_'.$instance->id.'.code'));
        $this->assertSame(12, session('eshop_cart_context_instance_'.$instance->id.'.channel_id'));
    }
}
