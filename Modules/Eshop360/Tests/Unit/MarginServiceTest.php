<?php

namespace Modules\Eshop360\Tests\Unit;

use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\Channel\Models\DistributionChannel;
use Modules\Eshop360\Domain\Sales\Models\Order;
use Modules\Eshop360\Domain\Sales\Models\OrderItem;
use Modules\Eshop360\Services\MarginService;
use Modules\Eshop360\Tests\TestCase;

final class MarginServiceTest extends TestCase
{
    private function makeChannel(array $overrides = []): DistributionChannel
    {
        $instance = CurrentInstance::get();

        return DistributionChannel::create(array_merge([
            'instance_id' => $instance->id,
            'name' => 'Canal Test',
            'slug' => 'canal-test',
            'code' => 'CH-TEST',
            'is_active' => true,
            'margin_rate' => 0.13,
            'buy_rate' => 0.20,
            'debt_share' => 0.3334,
            'channel_share' => 0.3333,
            'owner_share' => 0.3333,
        ], $overrides));
    }

    private function makeProduct(array $overrides = []): Product
    {
        $instance = CurrentInstance::get();

        return Product::create(array_merge([
            'instance_id' => $instance->id,
            'name' => 'Produit Margin',
            'slug' => 'produit-margin',
            'sku' => 'PM-001',
            'price' => 120,
            'cost_price' => 80,
            'pght' => 100,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'box',
            'min_quantity' => 0,
            'alert_quantity' => 5,
            'is_active' => true,
        ], $overrides));
    }

    private function makeOrderWithItems(int $instanceId, ?int $channelId, array $items): Order
    {
        $order = Order::create([
            'instance_id' => $instanceId,
            'order_number' => 'ORD-'.uniqid(),
            'status' => 'completed',
            'channel_id' => $channelId,
            'subtotal' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total' => 0,
        ]);

        $total = 0;
        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'product_name' => $item['name'] ?? 'Product',
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount' => 0,
                'tax' => 0,
                'total' => $item['unit_price'] * $item['quantity'],
            ]);
            $total += $item['unit_price'] * $item['quantity'];
        }

        $order->update(['subtotal' => $total, 'total' => $total]);

        return $order->fresh();
    }

    public function test_calculate_tripartite_margin_splits_correctly(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $channel = $this->makeChannel([
            'debt_share' => 0.3334,
            'channel_share' => 0.3333,
            'owner_share' => 0.3333,
        ]);

        $product = $this->makeProduct(['pght' => 100]);

        // unit_price=150, pght=100 => margin per unit = 50, qty 2 => total margin = 100
        $order = $this->makeOrderWithItems($instance->id, $channel->id, [
            ['product_id' => $product->id, 'name' => $product->name, 'quantity' => 2, 'unit_price' => 150],
        ]);

        $service = new MarginService;
        $log = $service->calculateTripartiteMargin($order, $channel);

        $this->assertNotNull($log);
        $this->assertSame(100.0, (float) $log->total_margin);
        $this->assertSame(33.34, (float) $log->debt_part);
        $this->assertSame(33.33, (float) $log->channel_part);
        $this->assertSame(33.33, (float) $log->owner_part);
    }

    public function test_margin_calculation_with_custom_shares(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $channel = $this->makeChannel([
            'debt_share' => 0.40,
            'channel_share' => 0.30,
            'owner_share' => 0.30,
        ]);

        $product = $this->makeProduct(['pght' => 80]);

        // unit_price=100, pght=80 => margin per unit = 20, qty 5 => total margin = 100
        $order = $this->makeOrderWithItems($instance->id, $channel->id, [
            ['product_id' => $product->id, 'name' => $product->name, 'quantity' => 5, 'unit_price' => 100],
        ]);

        $service = new MarginService;
        $log = $service->calculateTripartiteMargin($order, $channel);

        $this->assertNotNull($log);
        $this->assertSame(100.0, (float) $log->total_margin);
        $this->assertSame(40.0, (float) $log->debt_part);
        $this->assertSame(30.0, (float) $log->channel_part);
        $this->assertSame(30.0, (float) $log->owner_part);
    }

    public function test_sync_order_margins_creates_channel_margin_log(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $channel = $this->makeChannel([
            'debt_share' => 0.20,
            'channel_share' => 0.30,
            'owner_share' => 0.50,
        ]);

        $product = $this->makeProduct(['pght' => 100]);

        $order = $this->makeOrderWithItems($instance->id, $channel->id, [
            ['product_id' => $product->id, 'name' => $product->name, 'quantity' => 1, 'unit_price' => 200],
        ]);

        $service = new MarginService;
        $service->syncOrderMargins($order);

        $this->assertDatabaseHas('eshop_channel_margin_logs', [
            'order_id' => $order->id,
            'channel_id' => $channel->id,
            'total_margin' => 100.00,
            'debt_part' => 20.00,
            'channel_part' => 30.00,
            'owner_part' => 50.00,
        ]);
    }

    public function test_non_channel_order_has_no_margin_log(): void
    {
        [$instance] = $this->setUpInstanceWithAdmin();

        $product = $this->makeProduct();

        $order = $this->makeOrderWithItems($instance->id, null, [
            ['product_id' => $product->id, 'name' => $product->name, 'quantity' => 3, 'unit_price' => 150],
        ]);

        $service = new MarginService;
        $service->syncOrderMargins($order);

        $this->assertDatabaseMissing('eshop_channel_margin_logs', [
            'order_id' => $order->id,
        ]);
    }
}
