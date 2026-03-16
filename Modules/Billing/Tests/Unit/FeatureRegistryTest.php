<?php

namespace Modules\Billing\Tests\Unit;

use Modules\Billing\Models\Plan;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Services\FeatureRegistry;
use Modules\Billing\Services\SubscriptionManager;
use Modules\Billing\Tests\TestCase;
use Modules\Core\Hooks\DTO\BillableFeature;
use Modules\Core\Hooks\Registry\HookRegistry;

final class FeatureRegistryTest extends TestCase
{
    private HookRegistry $hookRegistry;
    private FeatureRegistry $featureRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hookRegistry = new HookRegistry();
        $subManager = app(SubscriptionManager::class);

        $this->featureRegistry = new FeatureRegistry($this->hookRegistry, $subManager);
    }

    public function test_free_feature_is_always_available(): void
    {
        $this->hookRegistry->addFeature(new BillableFeature(
            id: 'mod.free_feature',
            label: 'Free Feature',
            module: 'TestMod',
            tier: 'free',
        ));

        $this->assertTrue($this->featureRegistry->has('mod.free_feature', 999));
    }

    public function test_paid_feature_unavailable_without_subscription(): void
    {
        $this->hookRegistry->addFeature(new BillableFeature(
            id: 'mod.paid_feature',
            label: 'Paid Feature',
            module: 'TestMod',
            tier: 'paid',
        ));

        $this->assertFalse($this->featureRegistry->has('mod.paid_feature', 999));
    }

    public function test_paid_feature_available_with_matching_plan(): void
    {
        $this->hookRegistry->addFeature(new BillableFeature(
            id: 'mod.paid_feature',
            label: 'Paid Feature',
            module: 'TestMod',
            tier: 'paid',
        ));

        $plan = Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'price_monthly' => 5000,
            'price_yearly' => 50000,
            'trial_days' => 0,
            'features' => ['mod.paid_feature'],
            'is_active' => true,
        ]);

        $root = $this->makeRootInstance();

        Subscription::create([
            'instance_id' => $root->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
        ]);

        $this->featureRegistry->clearCache();
        $this->assertTrue($this->featureRegistry->has('mod.paid_feature', $root->id));
    }

    public function test_wildcard_plan_includes_all_features(): void
    {
        $this->hookRegistry->addFeature(new BillableFeature(
            id: 'mod.any_feature',
            label: 'Any',
            module: 'TestMod',
            tier: 'paid',
        ));

        $plan = Plan::create([
            'name' => 'Enterprise',
            'slug' => 'enterprise',
            'price_monthly' => 99000,
            'price_yearly' => 990000,
            'trial_days' => 0,
            'features' => ['*'],
            'is_active' => true,
        ]);

        $root = $this->makeRootInstance();

        Subscription::create([
            'instance_id' => $root->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
        ]);

        $this->featureRegistry->clearCache();
        $this->assertTrue($this->featureRegistry->has('mod.any_feature', $root->id));
    }

    public function test_all_returns_registered_features(): void
    {
        $this->hookRegistry->addFeature(new BillableFeature(id: 'a', label: 'A', module: 'T', tier: 'free'));
        $this->hookRegistry->addFeature(new BillableFeature(id: 'b', label: 'B', module: 'T', tier: 'paid'));

        $all = $this->featureRegistry->all();

        $this->assertCount(2, $all);
    }

    public function test_grouped_separates_free_and_paid(): void
    {
        $this->hookRegistry->addFeature(new BillableFeature(id: 'a', label: 'A', module: 'T', tier: 'free'));
        $this->hookRegistry->addFeature(new BillableFeature(id: 'b', label: 'B', module: 'T', tier: 'paid'));

        $grouped = $this->featureRegistry->grouped();

        $this->assertCount(1, $grouped['free']);
        $this->assertCount(1, $grouped['paid']);
    }

    public function test_missing_returns_paid_features_not_in_plan(): void
    {
        $this->hookRegistry->addFeature(new BillableFeature(id: 'a', label: 'A', module: 'T', tier: 'free'));
        $this->hookRegistry->addFeature(new BillableFeature(id: 'b', label: 'B', module: 'T', tier: 'paid'));
        $this->hookRegistry->addFeature(new BillableFeature(id: 'c', label: 'C', module: 'T', tier: 'paid'));

        $plan = Plan::create([
            'name' => 'Basic',
            'slug' => 'basic-test',
            'price_monthly' => 1000,
            'price_yearly' => 10000,
            'trial_days' => 0,
            'features' => ['b'],
            'is_active' => true,
        ]);

        $root = $this->makeRootInstance();

        Subscription::create([
            'instance_id' => $root->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
        ]);

        $this->featureRegistry->clearCache();
        $missing = $this->featureRegistry->missing($root->id);

        $this->assertCount(1, $missing);
        $this->assertEquals('c', $missing->first()->id);
    }

    public function test_cheapest_plan_for_feature(): void
    {
        $this->hookRegistry->addFeature(new BillableFeature(id: 'feat', label: 'F', module: 'T', tier: 'paid'));

        Plan::create([
            'name' => 'Expensive',
            'slug' => 'expensive',
            'price_monthly' => 99000,
            'price_yearly' => 990000,
            'features' => ['feat'],
            'is_active' => true,
        ]);

        $cheap = Plan::create([
            'name' => 'Cheap',
            'slug' => 'cheap',
            'price_monthly' => 5000,
            'price_yearly' => 50000,
            'features' => ['feat'],
            'is_active' => true,
        ]);

        $root = $this->makeRootInstance();
        $result = $this->featureRegistry->cheapestPlanFor('feat', $root->id);

        $this->assertNotNull($result);
        $this->assertEquals($cheap->id, $result->id);
    }
}
