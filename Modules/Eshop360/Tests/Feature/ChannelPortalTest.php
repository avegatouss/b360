<?php

namespace Modules\Eshop360\Tests\Feature;

use App\Models\User;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Eshop360\Models\ChannelProductPrice;
use Modules\Eshop360\Models\ChannelUser;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\Warehouse;
use Modules\Eshop360\Services\OrderService;
use Modules\Eshop360\Tests\TestCase;
use Spatie\Permission\Models\Permission;

final class ChannelPortalTest extends TestCase
{
    private function setUpChannelContext(): array
    {
        $instance = $this->makeRootInstance();
        $admin = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);

        foreach (['eshop.sales.view', 'eshop.sales.manage', 'eshop.products.view', 'eshop.products.manage'] as $perm) {
            Permission::findOrCreate($perm);
        }

        $channel = DistributionChannel::create([
            'instance_id' => $instance->id,
            'name' => 'Canal Portal',
            'slug' => 'canal-portal',
            'code' => 'CP-01',
            'is_active' => true,
            'margin_rate' => 0.13,
            'buy_rate' => 0.20,
            'debt_share' => 0.20,
            'channel_share' => 0.30,
            'owner_share' => 0.50,
            'portal_enabled' => true,
        ]);

        $warehouse = Warehouse::create([
            'instance_id' => $instance->id,
            'name' => 'Depot Canal',
            'code' => 'WH-CP',
            'is_active' => true,
        ]);

        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Produit Canal',
            'slug' => 'produit-canal',
            'sku' => 'PC-001',
            'price' => 100,
            'cost_price' => 60,
            'pght' => 80,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'unit',
            'min_quantity' => 0,
            'alert_quantity' => 5,
            'is_active' => true,
        ]);

        ChannelProductPrice::create([
            'channel_id' => $channel->id,
            'product_id' => $product->id,
            'sale_price' => 120,
            'is_manual_override' => true,
        ]);

        Stock::create([
            'instance_id' => $instance->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 50,
            'reserved_quantity' => 0,
        ]);

        return [$instance, $admin, $channel, $product, $warehouse];
    }

    private function makeMember(DistributionChannel $channel, string $role = 'member'): User
    {
        $user = User::create([
            'full_name' => 'Channel '.ucfirst($role),
            'email' => $role.'-'.uniqid().'@test.com',
            'password' => 'password',
        ]);

        ChannelUser::create([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'role' => $role,
        ]);

        return $user;
    }

    public function test_channel_member_can_access_portal(): void
    {
        [$instance, $admin, $channel] = $this->setUpChannelContext();

        $member = $this->makeMember($channel, 'member');

        // Attach member to instance
        \Illuminate\Support\Facades\DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instance->id,
            'user_id' => $member->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($member)
            ->get(route('eshop360.channel-portal.dashboard', [
                'slug' => $instance->slug,
                'channel' => $channel->id,
            ]));

        $response->assertOk();
    }

    public function test_non_member_cannot_access_portal(): void
    {
        [$instance, $admin, $channel] = $this->setUpChannelContext();

        $nonMember = User::create([
            'full_name' => 'Non Member',
            'email' => 'nonmember@test.com',
            'password' => 'password',
        ]);

        \Illuminate\Support\Facades\DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instance->id,
            'user_id' => $nonMember->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($nonMember)
            ->get(route('eshop360.channel-portal.dashboard', [
                'slug' => $instance->slug,
                'channel' => $channel->id,
            ]));

        // Should be forbidden or redirected
        $this->assertTrue(in_array($response->getStatusCode(), [403, 302]));
    }

    public function test_channel_dashboard_shows_stats(): void
    {
        [$instance, $admin, $channel, $product] = $this->setUpChannelContext();

        $member = $this->makeMember($channel, 'manager');

        \Illuminate\Support\Facades\DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instance->id,
            'user_id' => $member->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($member)
            ->get(route('eshop360.channel-portal.dashboard', [
                'slug' => $instance->slug,
                'channel' => $channel->id,
            ]));

        $response->assertOk();
    }

    public function test_channel_order_can_be_created(): void
    {
        [$instance, $admin, $channel, $product, $warehouse] = $this->setUpChannelContext();

        $member = $this->makeMember($channel, 'manager');

        \Illuminate\Support\Facades\DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instance->id,
            'user_id' => $member->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($member)
            ->post(route('eshop360.channel-portal.orders.store', [
                'slug' => $instance->slug,
                'channel' => $channel->id,
            ]), [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                    ],
                ],
                'notes' => 'Channel portal order',
            ]);

        $response->assertRedirect();

        $order = Order::withoutGlobalScopes()->where('channel_id', $channel->id)->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertSame($channel->id, $order->channel_id);
    }

    public function test_channel_order_reception_triggers_margin(): void
    {
        [$instance, $admin, $channel, $product, $warehouse] = $this->setUpChannelContext();

        // Create channel order using OrderService
        $this->actingAs($admin);

        $order = app(OrderService::class)->createFromItems([
            ['product_id' => $product->id, 'quantity' => 1],
        ], [
            'instance_id' => $instance->id,
            'status' => 'completed',
            'channel_id' => $channel->id,
            'source' => 'manual',
            'biller_id' => $admin->id,
            'payment_method' => 'cash',
            'paid_amount' => 120,
        ]);

        // Verify margin log was created
        $this->assertDatabaseHas('eshop_channel_margin_logs', [
            'order_id' => $order->id,
            'channel_id' => $channel->id,
        ]);
    }
}
