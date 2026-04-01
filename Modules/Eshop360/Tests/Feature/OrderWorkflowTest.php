<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Eshop360\Models\ChannelProductPrice;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\Warehouse;
use Modules\Eshop360\Services\OrderService;
use Modules\Eshop360\Tests\TestCase;
use Spatie\Permission\Models\Permission;

final class OrderWorkflowTest extends TestCase
{
    private function setUpInstanceAndUser(): array
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);

        foreach (['eshop.sales.view', 'eshop.sales.manage', 'eshop.products.view'] as $perm) {
            Permission::findOrCreate($perm);
        }

        return [$instance, $user];
    }

    private function makeWarehouseAndProduct(int $instanceId, int $stockQty = 50): array
    {
        $warehouse = Warehouse::create([
            'instance_id' => $instanceId,
            'name' => 'Depot',
            'code' => 'WH-01',
            'is_active' => true,
        ]);

        $product = Product::create([
            'instance_id' => $instanceId,
            'name' => 'Produit Test',
            'slug' => 'produit-test',
            'sku' => 'PT-001',
            'price' => 100,
            'cost_price' => 60,
            'pght' => 70,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'unit',
            'min_quantity' => 0,
            'alert_quantity' => 5,
            'is_active' => true,
        ]);

        Stock::create([
            'instance_id' => $instanceId,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => $stockQty,
            'reserved_quantity' => 0,
        ]);

        return [$warehouse, $product];
    }

    public function test_order_can_be_created(): void
    {
        [$instance, $user] = $this->setUpInstanceAndUser();
        [$warehouse, $product] = $this->makeWarehouseAndProduct($instance->id);

        $response = $this->actingAs($user)
            ->post(route('eshop360.sales.store', $instance->slug), [
                'payment_method' => 'cash',
                'paid_amount' => 200,
                'source' => 'pos',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                    ],
                ],
            ]);

        $response->assertRedirect();

        $order = Order::latest('id')->firstOrFail();
        $this->assertSame('completed', $order->status);
        $this->assertSame(200.0, (float) $order->total);
        $this->assertCount(1, $order->items);

        // Stock should have decreased
        $this->assertDatabaseHas('eshop_stocks', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 48,
        ]);
    }

    public function test_order_status_can_be_updated(): void
    {
        [$instance, $user] = $this->setUpInstanceAndUser();
        [$warehouse, $product] = $this->makeWarehouseAndProduct($instance->id);

        $this->actingAs($user);

        $order = app(OrderService::class)->createFromItems([
            ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100],
        ], [
            'instance_id' => $instance->id,
            'status' => 'pending',
            'source' => 'manual',
            'biller_id' => $user->id,
        ], false);

        $this->assertSame('pending', $order->status);

        // Update status directly
        $order->update(['status' => 'completed']);
        $order->refresh();

        $this->assertSame('completed', $order->status);
    }

    public function test_order_return_adjusts_stock(): void
    {
        [$instance, $user] = $this->setUpInstanceAndUser();
        [$warehouse, $product] = $this->makeWarehouseAndProduct($instance->id, 20);

        $this->actingAs($user);

        // Create initial sale
        $order = app(OrderService::class)->createFromItems([
            ['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 100],
        ], [
            'instance_id' => $instance->id,
            'status' => 'completed',
            'payment_method' => 'cash',
            'paid_amount' => 300,
            'source' => 'pos',
            'biller_id' => $user->id,
        ]);

        // Stock after sale: 20 - 3 = 17
        $this->assertDatabaseHas('eshop_stocks', [
            'product_id' => $product->id,
            'quantity' => 17,
        ]);

        // Process return
        $response = $this->post(route('eshop360.sales.returns.store', $instance->slug), [
            'order_id' => $order->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'reason' => 'Defective',
                ],
            ],
            'refund_amount' => 100,
            'notes' => 'Return defective item',
        ]);

        $response->assertRedirect();

        // Stock should increase by 1: 17 + 1 = 18
        $this->assertDatabaseHas('eshop_stocks', [
            'product_id' => $product->id,
            'quantity' => 18,
        ]);
    }

    public function test_channel_order_creates_margin_log(): void
    {
        [$instance, $user] = $this->setUpInstanceAndUser();
        [$warehouse, $product] = $this->makeWarehouseAndProduct($instance->id);

        $channel = DistributionChannel::create([
            'instance_id' => $instance->id,
            'name' => 'Canal Marge',
            'slug' => 'canal-marge',
            'code' => 'CM-01',
            'is_active' => true,
            'margin_rate' => 0.13,
            'buy_rate' => 0.20,
            'debt_share' => 0.20,
            'channel_share' => 0.30,
            'owner_share' => 0.50,
        ]);

        ChannelProductPrice::create([
            'channel_id' => $channel->id,
            'product_id' => $product->id,
            'sale_price' => 120,
            'is_manual_override' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('eshop360.sales.store', $instance->slug), [
                'payment_method' => 'cash',
                'paid_amount' => 120,
                'source' => 'manual',
                'channel_id' => $channel->id,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                    ],
                ],
            ]);

        $response->assertRedirect();

        $order = Order::with('channelMarginLogs')->latest('id')->firstOrFail();
        $this->assertSame($channel->id, $order->channel_id);
        $this->assertCount(1, $order->channelMarginLogs);

        $this->assertDatabaseHas('eshop_channel_margin_logs', [
            'order_id' => $order->id,
            'channel_id' => $channel->id,
        ]);
    }
}
