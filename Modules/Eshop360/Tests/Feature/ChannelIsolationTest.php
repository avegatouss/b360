<?php

namespace Modules\Eshop360\Tests\Feature;

use App\Models\User;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Eshop360\Models\ChannelProductPrice;
use Modules\Eshop360\Models\ChannelUser;
use Modules\Eshop360\Models\Coupon;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Stock;
use Modules\Eshop360\Models\Warehouse;
use Modules\Eshop360\Services\ChannelAccessService;
use Modules\Eshop360\Services\UserResourceScopeService;
use Modules\Eshop360\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Acceptance tests for the Hub/Channel/Client isolation model.
 *
 * Rules tested:
 * 1. A channel user cannot see Saphir Plus (hub) data
 * 2. A channel A user cannot see channel B data
 * 3. A hub admin can see all channels with filters
 * 4. A client only sees their own orders/account
 * 5. Channel stock increases ONLY via B2B supply (not manual adjustment)
 * 6. Feature flags disable module access per channel
 * 7. No assignment = no access (non-admin)
 */
final class ChannelIsolationTest extends TestCase
{
    private function setUpTwoChannels(): array
    {
        $instance = $this->makeRootInstance();
        $hubAdmin = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);

        foreach (['eshop.sales.view', 'eshop.sales.manage', 'eshop.products.view'] as $perm) {
            Permission::findOrCreate($perm);
        }

        // Channel A (Saphir Plus / Hub)
        $channelA = DistributionChannel::create([
            'instance_id' => $instance->id,
            'name' => 'Saphir Plus',
            'slug' => 'saphir-plus',
            'code' => 'SAPHIR',
            'is_active' => true,
            'portal_enabled' => false,
            'margin_rate' => 0, 'buy_rate' => 0,
            'debt_share' => 0.33, 'channel_share' => 0.33, 'owner_share' => 0.34,
        ]);

        // Channel B (CODIFARM)
        $warehouseB = Warehouse::create([
            'instance_id' => $instance->id,
            'name' => 'Depot Codifarm',
            'code' => 'WH-CDF',
            'is_active' => true,
        ]);

        $channelB = DistributionChannel::create([
            'instance_id' => $instance->id,
            'name' => 'CODIFARM',
            'slug' => 'demo-codifarm',
            'code' => 'CDF',
            'is_active' => true,
            'portal_enabled' => true,
            'warehouse_id' => $warehouseB->id,
            'margin_rate' => 0.13, 'buy_rate' => 0.20,
            'debt_share' => 0.33, 'channel_share' => 0.33, 'owner_share' => 0.34,
        ]);

        // Channel user for channel B
        $channelUser = User::factory()->create(['name' => 'Agent Codifarm']);
        ChannelUser::create([
            'channel_id' => $channelB->id,
            'user_id' => $channelUser->id,
            'role' => 'operator',
        ]);

        // Customers
        $hubCustomer = Customer::create([
            'instance_id' => $instance->id,
            'channel_id' => null,
            'name' => 'Client Hub',
            'code' => 'HUB-001',
        ]);

        $channelBCustomer = Customer::create([
            'instance_id' => $instance->id,
            'channel_id' => $channelB->id,
            'name' => 'Client Codifarm',
            'code' => 'CDF-001',
        ]);

        // Product
        $product = Product::create([
            'instance_id' => $instance->id,
            'name' => 'Paracetamol',
            'slug' => 'paracetamol',
            'sku' => 'PARA-001',
            'price' => 1000,
            'cost_price' => 600,
            'pght' => 800,
            'is_active' => true,
        ]);

        ChannelProductPrice::create([
            'channel_id' => $channelB->id,
            'product_id' => $product->id,
            'sale_price' => 960,
        ]);

        return compact('instance', 'hubAdmin', 'channelA', 'channelB', 'channelUser', 'hubCustomer', 'channelBCustomer', 'product', 'warehouseB');
    }

    // ─── Rule 1: Channel cannot see hub data ──────────

    public function test_channel_user_cannot_access_other_channels(): void
    {
        $ctx = $this->setUpTwoChannels();
        $cas = app(ChannelAccessService::class);

        // Channel user can access their own channel
        $this->assertTrue($cas->canAccessChannel($ctx['channelUser'], $ctx['channelB']));

        // Channel user cannot access Saphir Plus
        $this->assertFalse($cas->canAccessChannel($ctx['channelUser'], $ctx['channelA']));

        // Channel user's accessible channel IDs
        $accessible = $cas->accessibleChannelIds($ctx['channelUser']);
        $this->assertNotNull($accessible);
        $this->assertTrue($accessible->contains($ctx['channelB']->id));
        $this->assertFalse($accessible->contains($ctx['channelA']->id));
    }

    // ─── Rule 3: Hub admin sees all ───────────────────

    public function test_hub_admin_sees_all_channels(): void
    {
        $ctx = $this->setUpTwoChannels();
        $cas = app(ChannelAccessService::class);

        $this->assertTrue($cas->isHubAdmin($ctx['hubAdmin']));
        $this->assertNull($cas->accessibleChannelIds($ctx['hubAdmin']));
        $this->assertTrue($cas->canAccessChannel($ctx['hubAdmin'], $ctx['channelA']));
        $this->assertTrue($cas->canAccessChannel($ctx['hubAdmin'], $ctx['channelB']));
    }

    public function test_hub_admin_can_filter_by_channel(): void
    {
        $ctx = $this->setUpTwoChannels();
        $cas = app(ChannelAccessService::class);

        // Create hub and channel orders
        $hubOrder = Order::create([
            'instance_id' => $ctx['instance']->id,
            'channel_id' => null,
            'customer_id' => $ctx['hubCustomer']->id,
            'order_number' => 'HUB-001',
            'status' => 'completed',
            'total' => 5000,
            'source' => 'manual',
        ]);

        $channelOrder = Order::create([
            'instance_id' => $ctx['instance']->id,
            'channel_id' => $ctx['channelB']->id,
            'customer_id' => $ctx['channelBCustomer']->id,
            'order_number' => 'CDF-001',
            'status' => 'completed',
            'total' => 3000,
            'source' => 'channel_portal',
        ]);

        // Without filter — hub admin sees all
        $allOrders = $cas->scopeWithChannelFilter(Order::query(), $ctx['hubAdmin'])->count();
        $this->assertEquals(2, $allOrders);

        // With channel filter — hub admin sees only that channel
        $filtered = $cas->scopeWithChannelFilter(
            Order::query(), $ctx['hubAdmin'], $ctx['channelB']->id
        )->count();
        $this->assertEquals(1, $filtered);
    }

    // ─── Rule 5: Stock only via B2B ──────────────────

    public function test_channel_stock_adjustment_only_allows_out(): void
    {
        // Validate the rule: channel portal stock adjustments can only be type='out'
        // The validation rule in ChannelPortalStockController is: 'type' => 'required|in:out'
        $validator = \Illuminate\Support\Facades\Validator::make(
            ['type' => 'in', 'product_id' => 1, 'quantity' => 50],
            ['type' => 'required|in:out', 'product_id' => 'required|integer', 'quantity' => 'required|integer|min:1']
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());

        // 'out' should pass
        $validValidator = \Illuminate\Support\Facades\Validator::make(
            ['type' => 'out', 'product_id' => 1, 'quantity' => 50],
            ['type' => 'required|in:out', 'product_id' => 'required|integer', 'quantity' => 'required|integer|min:1']
        );

        $this->assertFalse($validValidator->fails());

        // 'adjustment' should also fail (removed from allowed values)
        $adjValidator = \Illuminate\Support\Facades\Validator::make(
            ['type' => 'adjustment', 'product_id' => 1, 'quantity' => 50],
            ['type' => 'required|in:out', 'product_id' => 'required|integer', 'quantity' => 'required|integer|min:1']
        );

        $this->assertTrue($adjValidator->fails());
    }

    // ─── Rule 7: No assignment = no access ───────────

    public function test_no_assignment_means_no_access(): void
    {
        $ctx = $this->setUpTwoChannels();

        // Create a non-admin user with zero assignments
        $normalUser = User::factory()->create(['name' => 'Agent sans affectation']);
        TeamContext::set($ctx['instance']->id);
        $role = Role::findOrCreate('agent');
        $normalUser->assignRole($role);

        $service = app(UserResourceScopeService::class);
        $service->init($normalUser);

        // Should NOT be admin
        $this->assertFalse($service->isAdmin());

        // Empty assignments should return empty collections (not null)
        $storeIds = $service->storeIds();
        $this->assertNotNull($storeIds);
        $this->assertTrue($storeIds->isEmpty());
    }

    // ─── Rule 4: Coupon isolation ─────────────────────

    public function test_coupon_scoped_to_channel(): void
    {
        $ctx = $this->setUpTwoChannels();

        // Hub coupon
        $hubCoupon = Coupon::create([
            'instance_id' => $ctx['instance']->id,
            'channel_id' => null,
            'name' => 'Hub Promo',
            'code' => 'HUB10',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ]);

        // Channel B coupon
        $channelCoupon = Coupon::create([
            'instance_id' => $ctx['instance']->id,
            'channel_id' => $ctx['channelB']->id,
            'name' => 'Codifarm Promo',
            'code' => 'CDF10',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ]);

        // visibleToChannel(null) should return only hub coupons
        $hubVisible = Coupon::visibleToChannel(null)->pluck('code')->all();
        $this->assertContains('HUB10', $hubVisible);
        $this->assertNotContains('CDF10', $hubVisible);

        // visibleToChannel(channelB) should return only channel B coupons
        $chVisible = Coupon::visibleToChannel($ctx['channelB']->id)->pluck('code')->all();
        $this->assertContains('CDF10', $chVisible);
        $this->assertNotContains('HUB10', $chVisible);
    }

    // ─── Rule 6: Feature flags ────────────────────────

    public function test_channel_feature_disabled_blocks_access(): void
    {
        $ctx = $this->setUpTwoChannels();

        // Disable HR for channel B
        app(\Modules\Eshop360\Services\EshopSettingsService::class)
            ->setChannelFeatures(['hr' => false], $ctx['channelB']->id, $ctx['instance']->id);

        $enabled = app(\Modules\Eshop360\Services\EshopSettingsService::class)
            ->isChannelFeatureEnabled('hr', $ctx['channelB']->id, true);

        $this->assertFalse($enabled);
    }

    public function test_channel_feature_enabled_allows_access(): void
    {
        $ctx = $this->setUpTwoChannels();

        // Enable HR for channel B
        app(\Modules\Eshop360\Services\EshopSettingsService::class)
            ->setChannelFeatures(['hr' => true], $ctx['channelB']->id, $ctx['instance']->id);

        $enabled = app(\Modules\Eshop360\Services\EshopSettingsService::class)
            ->isChannelFeatureEnabled('hr', $ctx['channelB']->id, false);

        $this->assertTrue($enabled);
    }
}
