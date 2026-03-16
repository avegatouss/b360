<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Eshop360\Models\CodifarmMarginLog;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\OrderItem;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Tests\TestCase;

final class CodifarmAndReportsTest extends TestCase
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

    public function test_codifarm_dashboard_and_orders_use_codifarm_logs_and_order_routes(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);

        $order = Order::create([
            'instance_id' => $instance->id,
            'order_number' => 'CODI-001',
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
            'is_codifarm' => true,
        ]);

        CodifarmMarginLog::create([
            'instance_id' => $instance->id,
            'order_id' => $order->id,
            'total_margin' => 1500,
            'debt_part' => 500,
            'codifarm_part' => 600,
            'saphir_part' => 400,
        ]);

        $this->actingAs($user)
            ->get(route('eshop360.codifarm.dashboard', ['slug' => $instance->slug]))
            ->assertOk()
            ->assertSee('1 500 XAF', false)
            ->assertSee('600 XAF', false)
            ->assertSee('400 XAF', false)
            ->assertSee('CODI-001');

        $this->actingAs($user)
            ->get(route('eshop360.codifarm.orders', ['slug' => $instance->slug]))
            ->assertOk()
            ->assertSee('CODI-001')
            ->assertSee('1 500 XAF', false)
            ->assertSee(route('eshop360.orders.show', ['slug' => $instance->slug, 'order' => $order]), false);
    }
}
