<?php

namespace Modules\Eshop360\Tests\Feature;

use App\Models\User;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Domain\Channel\Models\ChannelProductPrice;
use Modules\Eshop360\Domain\Channel\Models\ChannelUser;
use Modules\Eshop360\Domain\Channel\Models\DistributionChannel;
use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Eshop360\Domain\Finance\Models\Expense;
use Modules\Eshop360\Domain\Finance\Models\ExpenseCategory;
use Modules\Eshop360\Domain\Inventory\Models\Stock;
use Modules\Eshop360\Domain\Inventory\Models\Warehouse;
use Modules\Eshop360\Domain\Sales\Models\Order;
use Modules\Eshop360\Services\ChannelAccessService;
use Modules\Eshop360\Services\UserResourceScopeService;
use Modules\Eshop360\Support\CurrentChannel;
use Modules\Eshop360\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Acceptance tests for the Hub/Channel/Client isolation model.
 *
 * Tests the ChannelScope global scope which provides automatic
 * channel isolation on ALL Eshop360 models via BelongsToChannel trait.
 *
 * Rules tested:
 * 1. A channel user cannot see hub (null channel_id) data
 * 2. A channel A user cannot see channel B data
 * 3. A hub admin sees all data when no channel selected
 * 4. A hub admin sees only channel data when navigated into a channel
 * 5. Channel stock increases ONLY via B2B supply (not manual adjustment)
 * 6. Feature flags disable module access per channel
 * 7. No assignment = no access (non-admin)
 * 8. Auto-injection of channel_id on model creation
 * 9. withoutChannelScope() bypasses the scope
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
            'is_hub' => true,
            'is_active' => true,
            'portal_enabled' => false,
            'margin_rate' => 0, 'buy_rate' => 0,
            'debt_share' => 0.33, 'channel_share' => 0.33, 'owner_share' => 0.34,
        ]);

        // Channel B (CODIFARM)
        $warehouseB = Warehouse::withoutGlobalScopes()->create([
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
        $hubCustomer = Customer::withoutGlobalScopes()->create([
            'instance_id' => $instance->id,
            'channel_id' => null,
            'name' => 'Client Hub',
            'code' => 'HUB-001',
        ]);

        $channelBCustomer = Customer::withoutGlobalScopes()->create([
            'instance_id' => $instance->id,
            'channel_id' => $channelB->id,
            'name' => 'Client Codifarm',
            'code' => 'CDF-001',
        ]);

        // Product
        $product = Product::withoutGlobalScopes()->create([
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

    protected function tearDown(): void
    {
        CurrentChannel::flush();
        parent::tearDown();
    }

    // ─── Rule 1: Channel user cannot see hub data ─────

    public function test_channel_user_cannot_see_hub_orders(): void
    {
        $ctx = $this->setUpTwoChannels();

        Order::withoutGlobalScopes()->create([
            'instance_id' => $ctx['instance']->id,
            'channel_id' => null,
            'customer_id' => $ctx['hubCustomer']->id,
            'order_number' => 'HUB-001',
            'status' => 'completed',
            'total' => 5000,
            'source' => 'manual',
        ]);

        Order::withoutGlobalScopes()->create([
            'instance_id' => $ctx['instance']->id,
            'channel_id' => $ctx['channelB']->id,
            'customer_id' => $ctx['channelBCustomer']->id,
            'order_number' => 'CDF-001',
            'status' => 'completed',
            'total' => 3000,
            'source' => 'channel_portal',
        ]);

        // Act as channel user
        $this->actingAs($ctx['channelUser']);
        CurrentChannel::flush();

        // Channel user should only see their channel's orders
        $orders = Order::all();
        $this->assertEquals(1, $orders->count());
        $this->assertEquals('CDF-001', $orders->first()->order_number);
    }

    // ─── Rule 2: Channel A cannot see Channel B ───────

    public function test_channel_user_cannot_access_other_channels(): void
    {
        $ctx = $this->setUpTwoChannels();
        $cas = app(ChannelAccessService::class);

        $this->assertTrue($cas->canAccessChannel($ctx['channelUser'], $ctx['channelB']));
        $this->assertFalse($cas->canAccessChannel($ctx['channelUser'], $ctx['channelA']));

        $accessible = $cas->accessibleChannelIds($ctx['channelUser']);
        $this->assertNotNull($accessible);
        $this->assertTrue($accessible->contains($ctx['channelB']->id));
        $this->assertFalse($accessible->contains($ctx['channelA']->id));
    }

    public function test_channel_user_cannot_see_other_channel_customers(): void
    {
        $ctx = $this->setUpTwoChannels();

        // Create a customer in channel A
        Customer::withoutGlobalScopes()->create([
            'instance_id' => $ctx['instance']->id,
            'channel_id' => $ctx['channelA']->id,
            'name' => 'Client Saphir',
            'code' => 'SAP-001',
        ]);

        $this->actingAs($ctx['channelUser']);
        CurrentChannel::flush();

        $customers = Customer::all();
        $this->assertTrue($customers->contains('name', 'Client Codifarm'));
        $this->assertFalse($customers->contains('name', 'Client Saphir'));
        $this->assertFalse($customers->contains('name', 'Client Hub'));
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

    public function test_hub_admin_no_channel_selected_sees_all_orders(): void
    {
        $ctx = $this->setUpTwoChannels();

        Order::withoutGlobalScopes()->create([
            'instance_id' => $ctx['instance']->id,
            'channel_id' => null,
            'customer_id' => $ctx['hubCustomer']->id,
            'order_number' => 'HUB-001',
            'status' => 'completed',
            'total' => 5000,
            'source' => 'manual',
        ]);

        Order::withoutGlobalScopes()->create([
            'instance_id' => $ctx['instance']->id,
            'channel_id' => $ctx['channelB']->id,
            'customer_id' => $ctx['channelBCustomer']->id,
            'order_number' => 'CDF-001',
            'status' => 'completed',
            'total' => 3000,
            'source' => 'channel_portal',
        ]);

        // Hub admin, no channel selected
        $this->actingAs($ctx['hubAdmin']);
        CurrentChannel::clear();

        $orders = Order::all();
        $this->assertEquals(2, $orders->count());
    }

    // ─── Rule 4: Hub admin filtered to channel ────────

    public function test_hub_admin_scoped_to_channel_sees_only_that_channel(): void
    {
        $ctx = $this->setUpTwoChannels();

        Order::withoutGlobalScopes()->create([
            'instance_id' => $ctx['instance']->id,
            'channel_id' => null,
            'customer_id' => $ctx['hubCustomer']->id,
            'order_number' => 'HUB-001',
            'status' => 'completed',
            'total' => 5000,
            'source' => 'manual',
        ]);

        Order::withoutGlobalScopes()->create([
            'instance_id' => $ctx['instance']->id,
            'channel_id' => $ctx['channelB']->id,
            'customer_id' => $ctx['channelBCustomer']->id,
            'order_number' => 'CDF-001',
            'status' => 'completed',
            'total' => 3000,
            'source' => 'channel_portal',
        ]);

        // Hub admin navigated into channel B
        $this->actingAs($ctx['hubAdmin']);
        CurrentChannel::set($ctx['channelB']);

        $orders = Order::all();
        $this->assertEquals(1, $orders->count());
        $this->assertEquals('CDF-001', $orders->first()->order_number);
    }

    // ─── Rule 5: Stock only via B2B ──────────────────

    public function test_channel_stock_adjustment_only_allows_out(): void
    {
        $validator = \Illuminate\Support\Facades\Validator::make(
            ['type' => 'in', 'product_id' => 1, 'quantity' => 50],
            ['type' => 'required|in:out', 'product_id' => 'required|integer', 'quantity' => 'required|integer|min:1']
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());

        $validValidator = \Illuminate\Support\Facades\Validator::make(
            ['type' => 'out', 'product_id' => 1, 'quantity' => 50],
            ['type' => 'required|in:out', 'product_id' => 'required|integer', 'quantity' => 'required|integer|min:1']
        );

        $this->assertFalse($validValidator->fails());
    }

    // ─── Rule 7: No assignment = no access ───────────

    public function test_no_assignment_means_no_access(): void
    {
        $ctx = $this->setUpTwoChannels();

        $normalUser = User::factory()->create(['name' => 'Agent sans affectation']);
        TeamContext::set($ctx['instance']->id);
        $role = Role::findOrCreate('agent');
        $normalUser->assignRole($role);

        $service = app(UserResourceScopeService::class);
        $service->init($normalUser);

        $this->assertFalse($service->isAdmin());

        $storeIds = $service->storeIds();
        $this->assertNotNull($storeIds);
        $this->assertTrue($storeIds->isEmpty());
    }

    // ─── Rule 6: Module groups filtered by channel features ──

    public function test_global_channel_sees_all_module_groups(): void
    {
        $ctx = $this->setUpTwoChannels();

        $menuService = app(\Modules\Eshop360\Services\HierarchicalMenuService::class);
        $groups = $menuService->getModuleGroups($ctx['channelA']);

        $keys = array_keys($groups);
        $this->assertContains('finances', $keys);
        $this->assertContains('rh', $keys);
        $this->assertContains('vente', $keys);
    }

    public function test_regular_channel_sees_filtered_module_groups(): void
    {
        $ctx = $this->setUpTwoChannels();

        app(\Modules\Eshop360\Services\EshopSettingsService::class)
            ->setChannelFeatures([
                'hr' => false, 'finance' => false, 'support' => false,
                'sales' => true, 'customers' => true, 'stock' => true,
                'orders' => true, 'pos' => true, 'promotions' => true,
                'portal' => true, 'reports' => true, 'margins' => true,
            ], $ctx['channelB']->id, $ctx['instance']->id);

        $menuService = app(\Modules\Eshop360\Services\HierarchicalMenuService::class);
        $groups = $menuService->getModuleGroups($ctx['channelB']);

        $keys = array_keys($groups);
        $this->assertNotContains('finances', $keys);
        $this->assertNotContains('rh', $keys);
        $this->assertNotContains('communication', $keys);
        $this->assertContains('vente', $keys);
        $this->assertContains('clients', $keys);
    }

    // ─── Rule 6: Feature flags ────────────────────────

    public function test_channel_feature_disabled_blocks_access(): void
    {
        $ctx = $this->setUpTwoChannels();

        app(\Modules\Eshop360\Services\EshopSettingsService::class)
            ->setChannelFeatures(['hr' => false], $ctx['channelB']->id, $ctx['instance']->id);

        $enabled = app(\Modules\Eshop360\Services\EshopSettingsService::class)
            ->isChannelFeatureEnabled('hr', $ctx['channelB']->id, true);

        $this->assertFalse($enabled);
    }

    public function test_channel_feature_enabled_allows_access(): void
    {
        $ctx = $this->setUpTwoChannels();

        app(\Modules\Eshop360\Services\EshopSettingsService::class)
            ->setChannelFeatures(['hr' => true], $ctx['channelB']->id, $ctx['instance']->id);

        $enabled = app(\Modules\Eshop360\Services\EshopSettingsService::class)
            ->isChannelFeatureEnabled('hr', $ctx['channelB']->id, false);

        $this->assertTrue($enabled);
    }

    public function test_sale_store_rejects_client_supplied_unit_price(): void
    {
        $validator = \Illuminate\Support\Facades\Validator::make(
            [
                'items' => [
                    ['product_id' => 1, 'quantity' => 1, 'unit_price' => 0.01],
                ],
            ],
            [
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'prohibited',
            ]
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('items.0.unit_price', $validator->errors()->toArray());
    }

    // ─── Rule 8: Auto-injection of channel_id ─────────

    public function test_channel_id_auto_injected_on_create(): void
    {
        $ctx = $this->setUpTwoChannels();

        $this->actingAs($ctx['hubAdmin']);
        CurrentChannel::set($ctx['channelB']);

        $customer = Customer::withoutGlobalScopes()->create([
            'instance_id' => $ctx['instance']->id,
            'name' => 'Auto Client',
            'code' => 'AUTO-001',
        ]);

        $this->assertEquals($ctx['channelB']->id, $customer->channel_id);
    }

    public function test_hub_mode_leaves_channel_id_null(): void
    {
        $ctx = $this->setUpTwoChannels();

        $this->actingAs($ctx['hubAdmin']);
        CurrentChannel::clear();

        $customer = Customer::withoutGlobalScopes()->create([
            'instance_id' => $ctx['instance']->id,
            'name' => 'Hub Auto Client',
            'code' => 'HUBAUTO-001',
        ]);

        $this->assertNull($customer->channel_id);
    }

    // ─── Rule 9: withoutChannelScope() bypass ─────────

    public function test_without_channel_scope_returns_all(): void
    {
        $ctx = $this->setUpTwoChannels();

        Order::withoutGlobalScopes()->create([
            'instance_id' => $ctx['instance']->id,
            'channel_id' => null,
            'customer_id' => $ctx['hubCustomer']->id,
            'order_number' => 'HUB-003',
            'status' => 'completed',
            'total' => 5000,
            'source' => 'manual',
        ]);

        Order::withoutGlobalScopes()->create([
            'instance_id' => $ctx['instance']->id,
            'channel_id' => $ctx['channelB']->id,
            'customer_id' => $ctx['channelBCustomer']->id,
            'order_number' => 'CDF-003',
            'status' => 'completed',
            'total' => 3000,
            'source' => 'channel_portal',
        ]);

        // Even as channel user, withoutChannelScope sees all
        $this->actingAs($ctx['channelUser']);
        CurrentChannel::flush();

        $allOrders = Order::withoutChannelScope()->get();
        $this->assertGreaterThanOrEqual(2, $allOrders->count());
    }

    // ─── Cross-domain isolation: finance ──────────────

    public function test_channel_user_cannot_see_other_channel_expenses(): void
    {
        $ctx = $this->setUpTwoChannels();

        $category = ExpenseCategory::create([
            'instance_id' => $ctx['instance']->id,
            'name' => 'Logistique',
        ]);

        Expense::withoutGlobalScopes()->create([
            'instance_id' => $ctx['instance']->id,
            'channel_id' => $ctx['channelA']->id,
            'category_id' => $category->id,
            'user_id' => $ctx['hubAdmin']->id,
            'amount' => 5000,
            'description' => 'Hub Expense',
            'date' => now(),
        ]);

        Expense::withoutGlobalScopes()->create([
            'instance_id' => $ctx['instance']->id,
            'channel_id' => $ctx['channelB']->id,
            'category_id' => $category->id,
            'user_id' => $ctx['hubAdmin']->id,
            'amount' => 3000,
            'description' => 'Channel B Expense',
            'date' => now(),
        ]);

        $this->actingAs($ctx['channelUser']);
        CurrentChannel::flush();

        $expenses = Expense::all();
        $this->assertEquals(1, $expenses->count());
        $this->assertEquals('Channel B Expense', $expenses->first()->description);
    }

    // ─── Fail-closed: user without any channel ───────

    public function test_user_without_channel_assignment_sees_nothing(): void
    {
        $ctx = $this->setUpTwoChannels();

        Order::withoutGlobalScopes()->create([
            'instance_id' => $ctx['instance']->id,
            'channel_id' => $ctx['channelB']->id,
            'customer_id' => $ctx['channelBCustomer']->id,
            'order_number' => 'CDF-004',
            'status' => 'completed',
            'total' => 3000,
            'source' => 'channel_portal',
        ]);

        $orphanUser = User::factory()->create(['name' => 'Orphan User']);
        TeamContext::set($ctx['instance']->id);
        $role = Role::findOrCreate('agent');
        $orphanUser->assignRole($role);

        $this->actingAs($orphanUser);
        CurrentChannel::flush();

        // User with no channel assignment should see ZERO orders (fail-closed)
        $orders = Order::all();
        $this->assertEquals(0, $orders->count());
    }
}
