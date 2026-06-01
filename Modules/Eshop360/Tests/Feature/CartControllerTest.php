<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\Channel\Models\ChannelProductPrice;
use Modules\Eshop360\Domain\Channel\Models\DistributionChannel;
use Modules\Eshop360\Domain\Inventory\Models\Stock;
use Modules\Eshop360\Domain\Inventory\Models\Warehouse;
use Modules\Eshop360\Domain\Promotions\Models\Coupon;
use Modules\Eshop360\Tests\TestCase;

final class CartControllerTest extends TestCase
{
    public function test_browser_cart_actions_round_trip_through_pos_page_and_scoped_session(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);

        $warehouse = Warehouse::create([
            'instance_id' => $instance->id,
            'name' => 'Depot Web',
            'code' => 'DEP-WEB',
            'is_active' => true,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Vitamine D',
            'slug' => 'vitamine-d',
            'sku' => 'VITD-001',
            'price' => 10,
            'cost_price' => 5,
            'tax_rate' => 0,
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'unit' => 'box',
            'min_quantity' => 0,
            'alert_quantity' => 2,
            'is_active' => true,
        ]);

        Stock::create([
            'instance_id' => $instance->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 12,
            'reserved_quantity' => 0,
        ]);

        $coupon = Coupon::create([
            'instance_id' => $instance->id,
            'name' => 'Coupon panier',
            'code' => 'PANIER4',
            'type' => 'fixed',
            'value' => 4,
            'usage_limit' => 10,
            'used_count' => 0,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->from(route('eshop360.pos.index', ['slug' => $instance->slug]))
            ->post(route('eshop360.cart.add', ['slug' => $instance->slug]), [
                'product_id' => $product->id,
                'quantity' => 2,
            ])
            ->assertRedirect(route('eshop360.pos.index', ['slug' => $instance->slug]));

        $this->assertSame(2, session('eshop_cart.'.$product->id.'.quantity'));
        $this->assertSame(9.0, (float) session('eshop_cart.'.$product->id.'.unit_price'));
        $this->assertSame(2, session('eshop_cart_instance_'.$instance->id.'.'.$product->id.'.quantity'));

        $this->actingAs($user)
            ->from(route('eshop360.pos.index', ['slug' => $instance->slug]))
            ->put(route('eshop360.cart.update', [
                'slug' => $instance->slug,
                'itemKey' => $product->id,
            ]), [
                'quantity' => 3,
            ])
            ->assertRedirect(route('eshop360.pos.index', ['slug' => $instance->slug]));

        $this->assertSame(3, session('eshop_cart.'.$product->id.'.quantity'));
        $this->assertSame(27.0, (float) session('eshop_cart.'.$product->id.'.total'));

        $this->actingAs($user)
            ->from(route('eshop360.pos.index', ['slug' => $instance->slug]))
            ->post(route('eshop360.cart.coupon', ['slug' => $instance->slug]), [
                'code' => $coupon->code,
            ])
            ->assertRedirect(route('eshop360.pos.index', ['slug' => $instance->slug]));

        $this->assertSame($coupon->code, session('eshop_cart_coupon.code'));
        $this->assertSame($coupon->code, session('eshop_cart_coupon_instance_'.$instance->id.'.code'));

        $this->actingAs($user)
            ->get(route('eshop360.pos.index', ['slug' => $instance->slug]))
            ->assertOk()
            ->assertSee('Terminal POS')
            ->assertSee('Coupon actif')
            ->assertSee($coupon->code)
            ->assertSee('23.00');

        $this->actingAs($user)
            ->from(route('eshop360.pos.index', ['slug' => $instance->slug]))
            ->delete(route('eshop360.cart.clear', ['slug' => $instance->slug]))
            ->assertRedirect(route('eshop360.pos.index', ['slug' => $instance->slug]));

        $this->assertFalse(session()->has('eshop_cart'));
        $this->assertFalse(session()->has('eshop_cart_coupon'));
        $this->assertFalse(session()->has('eshop_cart_instance_'.$instance->id));
        $this->assertFalse(session()->has('eshop_cart_coupon_instance_'.$instance->id));
    }

    public function test_browser_cart_can_apply_channel_context_and_display_active_tariff(): void
    {
        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);

        $warehouse = Warehouse::create([
            'instance_id' => $instance->id,
            'name' => 'Depot Canal Web',
            'code' => 'DEP-CH-WEB',
            'is_active' => true,
        ]);

        $channel = DistributionChannel::create([
            'instance_id' => $instance->id,
            'name' => 'Clinique partenaire',
            'slug' => 'clinique-partenaire',
            'code' => 'CLIN',
            'is_active' => true,
            'margin_rate' => 0.13,
            'buy_rate' => 0.20,
            'debt_share' => 0.20,
            'channel_share' => 0.30,
            'owner_share' => 0.50,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Gants steriles',
            'slug' => 'gants-steriles',
            'sku' => 'GST-001',
            'price' => 100,
            'cost_price' => 70,
            'pght' => 80,
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
            'sale_price' => 125,
            'is_manual_override' => true,
        ]);

        Stock::create([
            'instance_id' => $instance->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 8,
            'reserved_quantity' => 0,
        ]);

        $this->actingAs($user)
            ->from(route('eshop360.pos.index', [
                'slug' => $instance->slug,
                'channel_id' => $channel->id,
            ]))
            ->post(route('eshop360.cart.add', ['slug' => $instance->slug]), [
                'product_id' => $product->id,
                'quantity' => 1,
                'channel_id' => $channel->id,
            ])
            ->assertRedirect(route('eshop360.pos.index', [
                'slug' => $instance->slug,
                'channel_id' => $channel->id,
            ]));

        $this->assertSame(125.0, (float) session('eshop_cart.'.$product->id.'.unit_price'));
        $this->assertSame($channel->id, session('eshop_cart_context.channel_id'));
        $this->assertSame($channel->id, session('eshop_cart_context_instance_'.$instance->id.'.channel_id'));

        $this->actingAs($user)
            ->get(route('eshop360.pos.index', [
                'slug' => $instance->slug,
                'channel_id' => $channel->id,
            ]))
            ->assertOk()
            ->assertSee('125.00'); // Channel pricing applied correctly
    }
}
