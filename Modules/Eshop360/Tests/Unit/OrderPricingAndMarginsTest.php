<?php

namespace Modules\Eshop360\Tests\Unit;

use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\ChannelProductPrice;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Services\OrderService;
use Modules\Eshop360\Tests\TestCase;

final class OrderPricingAndMarginsTest extends TestCase
{
    public function test_create_from_items_uses_channel_price_and_creates_channel_margin_log(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);

        $this->actingAs($user);
        CurrentInstance::set($instance);

        $channel = DistributionChannel::create([
            'instance_id' => $instance->id,
            'name' => 'Canal Distribution',
            'slug' => 'canal-distribution',
            'code' => 'CH-DIST',
            'is_active' => true,
            'margin_rate' => 0.13,
            'buy_rate' => 0.20,
            'debt_share' => 0.20,
            'channel_share' => 0.30,
            'owner_share' => 0.50,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Masques chirurgicaux',
            'slug' => 'masques-chirurgicaux',
            'sku' => 'MASK-CH',
            'price' => 120,
            'cost_price' => 90,
            'pght' => 100,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'box',
            'min_quantity' => 0,
            'alert_quantity' => 1,
            'is_active' => true,
        ]);

        ChannelProductPrice::create([
            'channel_id' => $channel->id,
            'product_id' => $product->id,
            'sale_price' => 150,
            'is_manual_override' => true,
        ]);

        $order = app(OrderService::class)->createFromItems([
            [
                'product_id' => $product->id,
                'quantity' => 2,
            ],
        ], [
            'instance_id' => $instance->id,
            'status' => 'completed',
            'channel_id' => $channel->id,
            'source' => 'manual',
            'biller_id' => $user->id,
        ], false);

        $this->assertTrue($order->isChannelOrder());
        $this->assertSame($channel->id, $order->channel_id);
        $this->assertSame(300.0, (float) $order->total);
        $this->assertSame(150.0, (float) $order->items->first()->unit_price);
        $this->assertNotNull($order->channelMarginLogs->first());

        $this->assertDatabaseHas('eshop_channel_margin_logs', [
            'order_id' => $order->id,
            'channel_id' => $channel->id,
            'total_margin' => 100.00,
            'debt_part' => 20.00,
            'channel_part' => 30.00,
            'owner_part' => 50.00,
        ]);
    }
}
