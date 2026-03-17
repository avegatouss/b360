<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Eshop360\Models\ChannelProductPrice;
use Modules\Eshop360\Models\Coupon;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\Warehouse;
use Modules\Eshop360\Tests\TestCase;

final class CheckoutControllerTest extends TestCase
{
    public function test_process_creates_order_payment_and_stock_movements_from_legacy_cart_session(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);

        $warehouse = Warehouse::create([
            'instance_id' => $instance->id,
            'name' => 'Depot Central',
            'code' => 'DEP-CENTRAL',
            'is_active' => true,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Paracetamol 500',
            'slug' => 'paracetamol-500',
            'sku' => 'PARA-500',
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
            'name' => 'Promo caisse',
            'code' => 'PROMO3',
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
            ])
            ->post(route('eshop360.checkout.process', [
                'slug' => $instance->slug,
            ]), [
                'customer_name' => 'Jean Test',
                'customer_phone' => '0700000000',
                'payment_method' => 'cash',
                'paid_amount' => 18.8,
                'shipping_amount' => 2,
                'notes' => 'Paiement comptoir',
            ]);

        $response->assertStatus(302);

        $order = Order::with(['items', 'payments'])->firstOrFail();

        $response->assertRedirect(route('eshop360.orders.show', [
            'slug' => $instance->slug,
            'order' => $order,
        ]));

        $this->assertSame('completed', $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('pos', $order->source);
        $this->assertSame(20.0, (float) $order->subtotal);
        $this->assertSame(1.8, (float) $order->tax_amount);
        $this->assertSame(5.0, (float) $order->discount_amount);
        $this->assertSame(18.8, (float) $order->total);
        $this->assertCount(1, $order->items);
        $this->assertCount(1, $order->payments);

        $this->assertDatabaseHas('eshop_payments', [
            'payable_type' => Order::class,
            'payable_id' => $order->id,
            'amount' => 18.80,
            'method' => 'cash',
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

        $this->assertDatabaseHas('eshop_customers', [
            'instance_id' => $instance->id,
            'name' => 'Jean Test',
        ]);

        $this->assertDatabaseHas('eshop_coupons', [
            'id' => $coupon->id,
            'used_count' => 1,
        ]);
    }

    public function test_process_uses_cart_context_for_channel_pricing_and_margin_logs(): void
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
            'name' => 'Hopital partenaire',
            'slug' => 'hopital-partenaire',
            'code' => 'HOP-PT',
            'is_active' => true,
            'margin_rate' => 0.13,
            'buy_rate' => 0.20,
            'debt_share' => 0.20,
            'channel_share' => 0.30,
            'owner_share' => 0.50,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Perfuseur',
            'slug' => 'perfuseur',
            'sku' => 'PERF-01',
            'price' => 100,
            'cost_price' => 70,
            'pght' => 80,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'piece',
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
            'quantity' => 10,
            'reserved_quantity' => 0,
        ]);

        $response = $this->actingAs($user)
            ->withSession([
                'eshop_cart' => [
                    (string) $product->id => [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'unit_price' => 125,
                        'original_price' => 100,
                        'tax_rate' => 0,
                        'quantity' => 1,
                        'total' => 125,
                        'channel_id' => $channel->id,
                    ],
                ],
                'eshop_cart_context' => [
                    'channel_id' => $channel->id,
                ],
                'eshop_cart_instance_' . $instance->id => [
                    (string) $product->id => [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'unit_price' => 125,
                        'original_price' => 100,
                        'tax_rate' => 0,
                        'quantity' => 1,
                        'total' => 125,
                        'channel_id' => $channel->id,
                    ],
                ],
                'eshop_cart_context_instance_' . $instance->id => [
                    'channel_id' => $channel->id,
                ],
            ])
            ->post(route('eshop360.checkout.process', [
                'slug' => $instance->slug,
            ]), [
                'customer_name' => 'Clinique Test',
                'payment_method' => 'cash',
                'paid_amount' => 125,
            ]);

        $response->assertStatus(302);

        $order = Order::with(['items', 'channelMarginLogs'])->firstOrFail();

        $this->assertSame($channel->id, $order->channel_id);
        $this->assertSame(125.0, (float) $order->total);
        $this->assertSame(125.0, (float) $order->items->first()->unit_price);
        $this->assertCount(1, $order->channelMarginLogs);

        $this->assertDatabaseHas('eshop_channel_margin_logs', [
            'order_id' => $order->id,
            'channel_id' => $channel->id,
            'total_margin' => 45.00,
        ]);
    }
}
