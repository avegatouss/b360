<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Eshop360\Models\ChannelProductPrice;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\OnlineOrder;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Tests\TestCase;

final class OnlineOrderApiTest extends TestCase
{
    public function test_api_online_order_uses_channel_price_and_conversion_keeps_margin_context(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);

        $customer = Customer::create([
            'instance_id' => $instance->id,
            'code' => 'CUS-000100',
            'name' => 'Centre de soins',
            'email' => 'centre@example.test',
            'is_active' => true,
        ]);

        $channel = DistributionChannel::create([
            'instance_id' => $instance->id,
            'name' => 'Grossiste hopital',
            'slug' => 'grossiste-hopital',
            'code' => 'GHOP',
            'is_active' => true,
            'margin_rate' => 0.13,
            'buy_rate' => 0.20,
            'debt_share' => 0.20,
            'channel_share' => 0.30,
            'owner_share' => 0.50,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Set de perfusion',
            'slug' => 'set-de-perfusion',
            'sku' => 'SET-PERF',
            'price' => 100,
            'cost_price' => 70,
            'pght' => 80,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'piece',
            'min_quantity' => 0,
            'alert_quantity' => 1,
            'is_active' => true,
        ]);

        ChannelProductPrice::create([
            'channel_id' => $channel->id,
            'product_id' => $product->id,
            'sale_price' => 125,
            'is_manual_override' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('api.eshop360.orders.store'), [
            'instance_id' => $instance->id,
            'customer_id' => $customer->id,
            'channel_id' => $channel->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
            'delivery_address' => 'Avenue des cliniques',
        ]);

        $response->assertCreated();

        $onlineOrder = OnlineOrder::with('items')->firstOrFail();

        $this->assertSame($channel->id, $onlineOrder->channel_id);
        $this->assertTrue($onlineOrder->isChannelOrder());
        $this->assertSame(125.0, (float) $onlineOrder->total);
        $this->assertSame(125.0, (float) $onlineOrder->items->first()->unit_price);

        $onlineOrder->update(['status' => 'received']);

        $this->actingAs($user)
            ->patch(route('eshop360.online-orders.status', [
                'slug' => $instance->slug,
                'onlineOrder' => $onlineOrder,
            ]), [
                'status' => 'invoiced',
            ])
            ->assertRedirect();

        $order = Order::with('channelMarginLogs')->where('source', 'online')->firstOrFail();

        $this->assertSame($channel->id, $order->channel_id);
        $this->assertCount(1, $order->channelMarginLogs);

        $this->assertDatabaseHas('eshop_channel_margin_logs', [
            'order_id' => $order->id,
            'channel_id' => $channel->id,
            'total_margin' => 45.00,
        ]);
    }
}
