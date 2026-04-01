<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\ChannelProductPrice;
use Modules\Eshop360\Models\Coupon;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\Warehouse;
use Modules\Eshop360\Services\OrderService;
use Modules\Eshop360\Tests\TestCase;

final class SaleControllerTest extends TestCase
{
    public function test_store_creates_pos_sale_from_payload_clears_cart_and_tracks_coupon_usage(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);

        $warehouse = Warehouse::create([
            'instance_id' => $instance->id,
            'name' => 'Depot POS',
            'code' => 'DEP-POS',
            'is_active' => true,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Ibuprofen 400',
            'slug' => 'ibuprofen-400',
            'sku' => 'IBU-400',
            'price' => 10,
            'cost_price' => 6,
            'tax_rate' => 10,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'box',
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

        $coupon = Coupon::create([
            'instance_id' => $instance->id,
            'name' => 'Promo POS',
            'code' => 'PROMOPOS',
            'type' => 'fixed',
            'value' => 3,
            'usage_limit' => 10,
            'used_count' => 0,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->withSession([
                'eshop_cart' => [
                    (string) $product->id => [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'unit_price' => 9,
                        'original_price' => 10,
                        'tax_rate' => 10,
                        'quantity' => 2,
                        'total' => 18,
                    ],
                ],
                'eshop_cart_coupon' => [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'type' => 'fixed',
                    'value' => 3,
                ],
                'eshop_cart_instance_' . $instance->id => [
                    (string) $product->id => [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'unit_price' => 9,
                        'original_price' => 10,
                        'tax_rate' => 10,
                        'quantity' => 2,
                        'total' => 18,
                    ],
                ],
                'eshop_cart_coupon_instance_' . $instance->id => [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'type' => 'fixed',
                    'value' => 3,
                ],
            ])
            ->post(route('eshop360.sales.store', [
                'slug' => $instance->slug,
            ]), [
                'payment_method' => 'cash',
                'paid_amount' => 19,
                'discount_amount' => 3,
                'coupon_code' => $coupon->code,
                'source' => 'pos',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                    ],
                ],
            ]);

        $response->assertStatus(302);

        $order = Order::with(['items', 'payments'])->firstOrFail();

        $response->assertRedirect(route('eshop360.sales.show', [
            'slug' => $instance->slug,
            'order' => $order,
        ]));

        $this->assertStringStartsWith('SAL-', $order->order_number);
        $this->assertSame('completed', $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('pos', $order->source);
        $this->assertSame(20.0, (float) $order->subtotal);
        $this->assertSame(2.0, (float) $order->tax_amount);
        $this->assertSame(3.0, (float) $order->discount_amount);
        $this->assertSame(19.0, (float) $order->total);
        $this->assertCount(1, $order->items);
        $this->assertCount(1, $order->payments);

        $this->assertDatabaseHas('eshop_payments', [
            'payable_type' => Order::class,
            'payable_id' => $order->id,
            'amount' => 19.0,
            'method' => 'cash',
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('eshop_stock_movements', [
            'reference_type' => Order::class,
            'reference_id' => $order->id,
            'type' => 'out',
            'quantity' => -2,
        ]);

        $this->assertDatabaseHas('eshop_stocks', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 8,
        ]);

        $this->assertDatabaseHas('eshop_coupons', [
            'id' => $coupon->id,
            'used_count' => 1,
        ]);

        $this->assertFalse(session()->has('eshop_cart'));
        $this->assertFalse(session()->has('eshop_cart_coupon'));
        $this->assertFalse(session()->has('eshop_cart_instance_' . $instance->id));
        $this->assertFalse(session()->has('eshop_cart_coupon_instance_' . $instance->id));
    }

    public function test_store_resolves_channel_price_and_creates_margin_log_when_unit_price_is_omitted(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);

        $warehouse = Warehouse::create([
            'instance_id' => $instance->id,
            'name' => 'Depot Canal',
            'code' => 'DEP-CH',
            'is_active' => true,
        ]);

        $channel = DistributionChannel::create([
            'instance_id' => $instance->id,
            'name' => 'Hopital',
            'slug' => 'hopital',
            'code' => 'HOP',
            'is_active' => true,
            'margin_rate' => 0.13,
            'buy_rate' => 0.20,
            'debt_share' => 0.20,
            'channel_share' => 0.30,
            'owner_share' => 0.50,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Kit Seringues',
            'slug' => 'kit-seringues',
            'sku' => 'KIT-SER',
            'price' => 100,
            'cost_price' => 70,
            'pght' => 80,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'box',
            'min_quantity' => 0,
            'alert_quantity' => 2,
            'is_active' => true,
        ]);

        ChannelProductPrice::create([
            'channel_id' => $channel->id,
            'product_id' => $product->id,
            'sale_price' => 125,
            'is_manual_override' => true,
        ]);

        Stock::create([
            'instance_id' => $instance->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
            'reserved_quantity' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('eshop360.sales.store', [
            'slug' => $instance->slug,
        ]), [
            'payment_method' => 'cash',
            'paid_amount' => 125,
            'source' => 'manual',
            'channel_id' => $channel->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(302);

        $order = Order::with(['items', 'channelMarginLogs'])->latest('id')->firstOrFail();

        $this->assertSame($channel->id, $order->channel_id);
        $this->assertSame(125.0, (float) $order->items->first()->unit_price);
        $this->assertSame(125.0, (float) $order->total);
        $this->assertCount(1, $order->channelMarginLogs);
        $this->assertSame(45.0, (float) $order->channelMarginLogs->first()->margin);

        $this->assertDatabaseHas('eshop_channel_margin_logs', [
            'order_id' => $order->id,
            'channel_id' => $channel->id,
            'total_margin' => 45.00,
            'debt_part' => 9.00,
            'channel_part' => 13.50,
            'owner_part' => 22.50,
        ]);
    }

    public function test_store_return_creates_negative_return_order_and_reinjects_stock(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);

        $warehouse = Warehouse::create([
            'instance_id' => $instance->id,
            'name' => 'Depot SAV',
            'code' => 'DEP-SAV',
            'is_active' => true,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Amoxicillin',
            'slug' => 'amoxicillin',
            'sku' => 'AMOX-500',
            'price' => 10,
            'cost_price' => 7,
            'tax_rate' => 10,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'box',
            'min_quantity' => 0,
            'alert_quantity' => 2,
            'is_active' => true,
        ]);

        Stock::create([
            'instance_id' => $instance->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
            'reserved_quantity' => 0,
        ]);

        $this->actingAs($user);
        CurrentInstance::set($instance);

        $order = app(OrderService::class)->createFromItems([
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => 10,
            ],
        ], [
            'instance_id' => $instance->id,
            'status' => 'completed',
            'payment_method' => 'cash',
            'paid_amount' => 22,
            'source' => 'pos',
            'biller_id' => $user->id,
        ]);

        $response = $this->post(route('eshop360.sales.returns.store', [
            'slug' => $instance->slug,
        ]), [
            'order_id' => $order->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'reason' => 'Boite endommagee',
                ],
            ],
            'refund_amount' => 11,
            'notes' => 'Retour partiel client',
        ]);

        $response->assertRedirect(route('eshop360.sales.returns', [
            'slug' => $instance->slug,
        ]));

        $returnOrder = Order::with(['items', 'payments'])
            ->where('status', 'refunded')
            ->whereKeyNot($order->id)
            ->firstOrFail();

        $this->assertSame('refunded', $order->fresh()->status);
        $this->assertSame('refunded', $returnOrder->status);
        $this->assertSame('paid', $returnOrder->payment_status);
        $this->assertSame(-11.0, (float) $returnOrder->subtotal);
        $this->assertSame(-11.0, (float) $returnOrder->total);
        $this->assertSame(-11.0, (float) $returnOrder->paid_amount);
        $this->assertCount(1, $returnOrder->items);
        $this->assertCount(1, $returnOrder->payments);
        $this->assertSame(-1, $returnOrder->items->first()->quantity);
        $this->assertSame(-11.0, (float) $returnOrder->items->first()->total);

        $this->assertDatabaseHas('eshop_payments', [
            'payable_type' => Order::class,
            'payable_id' => $returnOrder->id,
            'amount' => -11,
            'status' => 'refunded',
        ]);

        $this->assertDatabaseHas('eshop_stock_movements', [
            'reference_type' => Order::class,
            'reference_id' => $returnOrder->id,
            'type' => 'return',
            'quantity' => 1,
        ]);

        $this->assertDatabaseHas('eshop_stocks', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 4,
        ]);
    }
}
