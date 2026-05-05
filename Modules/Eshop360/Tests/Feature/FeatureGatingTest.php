<?php

namespace Modules\Eshop360\Tests\Feature;

use Modules\Billing\Models\Plan;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Services\FeatureRegistry;
use Modules\Billing\Services\SubscriptionManager;
use Modules\Core\Hooks\DTO\BillableFeature;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Tests\TestCase;
use Spatie\Permission\Models\Permission;

final class FeatureGatingTest extends TestCase
{
    private HookRegistry $hookRegistry;

    private FeatureRegistry $featureRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hookRegistry = new HookRegistry;
        $subManager = app(SubscriptionManager::class);
        $this->featureRegistry = new FeatureRegistry($this->hookRegistry, $subManager);
    }

    public function test_free_feature_accessible_without_paid_plan(): void
    {
        // 'products.crud' is a free feature — no plan needed.
        // A user with eshop.products.view permission should reach /products fine.

        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);

        Permission::findOrCreate('eshop.products.view');

        // Create a product so the page renders with content
        Product::create([
            'instance_id' => $instance->id,
            'name' => 'Free Access Produit',
            'slug' => 'free-access-produit',
            'sku' => 'FREE-001',
            'price' => 1000,
            'cost_price' => 600,
            'tax_rate' => 0,
            'discount_type' => 'none',
            'discount_value' => 0,
            'unit' => 'boite',
            'min_quantity' => 0,
            'alert_quantity' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->get(route('eshop360.products.index', $instance->slug));

        $response->assertOk();
        $response->assertSee('Free Access Produit');
    }

    public function test_free_feature_is_always_available_in_feature_gate(): void
    {
        // Unit-level: FeatureRegistry::has() returns true for free features
        // (wildcard plan handling + no-plan fallback).

        $this->hookRegistry->addFeature(new BillableFeature(
            id: 'eshop360.products.crud',
            label: 'Products CRUD',
            module: 'Eshop360',
            tier: 'free',
        ));

        $this->assertTrue($this->featureRegistry->has('eshop360.products.crud', 9999));
    }

    public function test_paid_feature_unavailable_without_subscription(): void
    {
        // Unit-level: a paid feature is NOT available for an instance with no active plan.

        $this->hookRegistry->addFeature(new BillableFeature(
            id: 'eshop360.hr',
            label: 'Module RH',
            module: 'Eshop360',
            tier: 'paid',
        ));

        $instance = $this->makeRootInstance();

        // No subscription created — feature must be unavailable.
        $this->featureRegistry->clearCache();
        $this->assertFalse($this->featureRegistry->has('eshop360.hr', $instance->id));
    }

    public function test_paid_feature_accessible_with_subscription(): void
    {
        // Unit-level: a paid feature IS available once a matching plan is subscribed.

        $this->hookRegistry->addFeature(new BillableFeature(
            id: 'eshop360.hr',
            label: 'Module RH',
            module: 'Eshop360',
            tier: 'paid',
        ));

        $plan = Plan::create([
            'name' => 'Pro HR',
            'slug' => 'pro-hr-test',
            'price_monthly' => 10000,
            'price_yearly' => 100000,
            'trial_days' => 0,
            'features' => ['eshop360.hr'],
            'is_active' => true,
        ]);

        $instance = $this->makeRootInstance();

        Subscription::create([
            'instance_id' => $instance->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
        ]);

        $this->featureRegistry->clearCache();
        $this->assertTrue($this->featureRegistry->has('eshop360.hr', $instance->id));
    }

    public function test_hr_employees_route_accessible_with_permission(): void
    {
        // The /hr/employees route is protected by 'can:eshop.hr.view'.
        // A super-admin with that permission should get 200.
        // Note: the EnsurePaidFeature middleware is NOT applied to HR routes in web.php —
        // feature gating is done at the middleware layer only for explicitly configured routes.

        $instance = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($instance);
        CurrentInstance::set($instance);
        TeamContext::set(0);

        foreach (['eshop.hr.view', 'eshop.hr.manage'] as $perm) {
            Permission::findOrCreate($perm);
        }

        $response = $this->actingAs($user)
            ->get(route('eshop360.hr.employees.index', $instance->slug));

        $response->assertOk();
    }

    public function test_wildcard_plan_grants_all_features(): void
    {
        // An Enterprise plan with features=['*'] should grant every registered feature.

        $this->hookRegistry->addFeature(new BillableFeature(
            id: 'eshop360.imports',
            label: 'Importations',
            module: 'Eshop360',
            tier: 'paid',
        ));

        $this->hookRegistry->addFeature(new BillableFeature(
            id: 'eshop360.channels',
            label: 'Canaux distribution',
            module: 'Eshop360',
            tier: 'paid',
        ));

        $plan = Plan::create([
            'name' => 'Enterprise Test',
            'slug' => 'enterprise-gate-test',
            'price_monthly' => 99000,
            'price_yearly' => 990000,
            'trial_days' => 0,
            'features' => ['*'],
            'is_active' => true,
        ]);

        $instance = $this->makeRootInstance();

        Subscription::create([
            'instance_id' => $instance->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
        ]);

        $this->featureRegistry->clearCache();
        $this->assertTrue($this->featureRegistry->has('eshop360.imports', $instance->id));
        $this->assertTrue($this->featureRegistry->has('eshop360.channels', $instance->id));
    }
}
