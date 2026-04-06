<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Eshop360\Models\ChannelMarginLog;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\OrderItem;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Tests\TestCase;

final class ChannelAndReportsTest extends TestCase
{
    public function test_pos_overview_report_aggregates_transactions_items_and_cashiers(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        $productA = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Produit A',
            'slug' => 'produit-a',
            'sku' => 'A',
            'price' => 10,
            'cost_price' => 5,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'unit',
            'min_quantity' => 0,
            'alert_quantity' => 1,
            'is_active' => true,
        ]);
        $productB = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Produit B',
            'slug' => 'produit-b',
            'sku' => 'B',
            'price' => 10,
            'cost_price' => 5,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'unit',
            'min_quantity' => 0,
            'alert_quantity' => 1,
            'is_active' => true,
        ]);

        $firstOrder = Order::create([
            'instance_id' => $instance->id,
            'order_number' => 'POS-001',
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'subtotal' => 20,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'total' => 20,
            'paid_amount' => 20,
            'due_amount' => 0,
            'source' => 'pos',
            'biller_id' => $user->id,
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);

        $secondOrder = Order::create([
            'instance_id' => $instance->id,
            'order_number' => 'POS-002',
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'card',
            'subtotal' => 10,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'total' => 10,
            'paid_amount' => 10,
            'due_amount' => 0,
            'source' => 'pos',
            'biller_id' => $user->id,
            'created_at' => now()->subMinutes(30),
            'updated_at' => now()->subMinutes(30),
        ]);

        OrderItem::create([
            'order_id' => $firstOrder->id,
            'product_id' => $productA->id,
            'product_name' => 'Produit A',
            'sku' => 'A',
            'quantity' => 2,
            'unit_price' => 10,
            'discount' => 0,
            'tax' => 0,
            'total' => 20,
        ]);

        OrderItem::create([
            'order_id' => $secondOrder->id,
            'product_id' => $productB->id,
            'product_name' => 'Produit B',
            'sku' => 'B',
            'quantity' => 1,
            'unit_price' => 10,
            'discount' => 0,
            'tax' => 0,
            'total' => 10,
        ]);

        $this->actingAs($user)
            ->get(route('eshop360.reports.pos-overview', [
                'slug' => $instance->slug,
                'from' => now()->startOfDay()->toDateString(),
                'to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Statistiques POS')
            ->assertSee('30.00')
            ->assertSee('3')
            ->assertSee('Admin');
    }

    public function test_channel_dashboard_and_orders_use_channel_margin_logs_and_order_routes(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);

        $channel = DistributionChannel::create([
            'instance_id' => $instance->id,
            'name' => 'Canal Rapport',
            'slug' => 'canal-rapport',
            'code' => 'CH-RPT',
            'is_active' => true,
            'margin_rate' => 0.13,
            'buy_rate' => 0.20,
            'debt_share' => 0.20,
            'channel_share' => 0.30,
            'owner_share' => 0.50,
        ]);

        $order = Order::create([
            'instance_id' => $instance->id,
            'order_number' => 'CH-001',
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'subtotal' => 5000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'total' => 5000,
            'paid_amount' => 5000,
            'due_amount' => 0,
            'source' => 'manual',
            'biller_id' => $user->id,
            'channel_id' => $channel->id,
        ]);

        ChannelMarginLog::create([
            'instance_id' => $instance->id,
            'channel_id' => $channel->id,
            'order_id' => $order->id,
            'total_margin' => 1500,
            'debt_part' => 500,
            'channel_part' => 600,
            'owner_part' => 400,
        ]);

        $this->actingAs($user)
            ->get(route('eshop360.channels.dashboard', ['slug' => $instance->slug, 'channel' => $channel]))
            ->assertOk()
            ->assertSee('1 500', false) // total_margin = 1500
            ->assertSee('500', false)   // debt_part = 500
            ->assertSee('CH-001');

        $this->actingAs($user)
            ->get(route('eshop360.channels.orders', ['slug' => $instance->slug, 'channel' => $channel]))
            ->assertOk()
            ->assertSee('CH-001')
            ->assertSee('5 000', false); // order total = 5000
    }
}
