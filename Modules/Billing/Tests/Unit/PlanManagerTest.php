<?php

namespace Modules\Billing\Tests\Unit;

use App\Instances\Instance;
use Modules\Billing\Models\Plan;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Services\PlanManager;
use Modules\Billing\Tests\TestCase;

final class PlanManagerTest extends TestCase
{
    private PlanManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = app(PlanManager::class);
    }

    public function test_create_plan(): void
    {
        $plan = $this->manager->create([
            'name' => 'Starter',
            'slug' => 'starter',
            'price_monthly' => 9.99,
            'trial_days' => 14,
        ]);

        $this->assertDatabaseHas('plans', [
            'name' => 'Starter',
            'slug' => 'starter',
            'price_monthly' => 9.99,
        ]);
        $this->assertInstanceOf(Plan::class, $plan);
    }

    public function test_create_plan_generates_slug(): void
    {
        $plan = $this->manager->create([
            'name' => 'Business Pro',
            'price_monthly' => 29.99,
            'trial_days' => 7,
        ]);

        $this->assertSame('business-pro', $plan->slug);
    }

    public function test_find_by_slug(): void
    {
        $this->manager->create([
            'name' => 'Enterprise',
            'slug' => 'enterprise',
            'price_monthly' => 99.99,
            'trial_days' => 30,
        ]);

        $found = $this->manager->findBySlug('enterprise');
        $this->assertNotNull($found);
        $this->assertSame('Enterprise', $found->name);
    }

    public function test_all_returns_only_active(): void
    {
        $this->manager->create(['name' => 'Active', 'price_monthly' => 10, 'trial_days' => 0, 'is_active' => true]);
        $this->manager->create(['name' => 'Inactive', 'price_monthly' => 10, 'trial_days' => 0, 'is_active' => false]);

        $active = $this->manager->all(activeOnly: true);
        $this->assertCount(1, $active);
        $this->assertSame('Active', $active->first()->name);
    }

    public function test_all_returns_all_when_flag_false(): void
    {
        $this->manager->create(['name' => 'Active', 'price_monthly' => 10, 'trial_days' => 0, 'is_active' => true]);
        $this->manager->create(['name' => 'Inactive', 'price_monthly' => 10, 'trial_days' => 0, 'is_active' => false]);

        $all = $this->manager->all(activeOnly: false);
        $this->assertCount(2, $all);
    }

    public function test_update_plan(): void
    {
        $plan = $this->manager->create(['name' => 'Old', 'price_monthly' => 5, 'trial_days' => 0]);

        $updated = $this->manager->update($plan, ['name' => 'New', 'price_monthly' => 15]);
        $this->assertSame('New', $updated->name);
        $this->assertEquals(15, $updated->price_monthly);
    }

    public function test_delete_plan_without_subscriptions(): void
    {
        $plan = $this->manager->create(['name' => 'Temp', 'price_monthly' => 1, 'trial_days' => 0]);

        $this->manager->delete($plan);
        $this->assertDatabaseMissing('plans', ['name' => 'Temp']);
    }

    public function test_delete_plan_with_subscriptions_soft_deletes(): void
    {
        $plan = $this->manager->create(['name' => 'InUse', 'price_monthly' => 10, 'trial_days' => 0]);

        Subscription::create([
            'instance_id' => 1,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
        ]);

        $this->manager->delete($plan);

        $this->assertDatabaseHas('plans', ['name' => 'InUse', 'is_active' => false]);
    }

    public function test_for_instance_returns_plans_visible_to_all(): void
    {
        $instance = $this->makeRootInstance();

        $this->manager->create(['name' => 'Global Plan', 'price_monthly' => 10, 'trial_days' => 0, 'visibility' => 'all']);
        $this->manager->create(['name' => 'Restricted', 'price_monthly' => 20, 'trial_days' => 0, 'visibility' => 'specific']);

        $plans = $this->manager->forInstance($instance->id);

        $this->assertCount(1, $plans);
        $this->assertSame('Global Plan', $plans->first()->name);
    }

    public function test_for_instance_returns_specific_plans_for_assigned_instance(): void
    {
        $instance = Instance::create([
            'name' => 'Client A',
            'slug' => 'client-a',
            'is_active' => true,
        ]);

        $plan = $this->manager->create(['name' => 'VIP Plan', 'price_monthly' => 50, 'trial_days' => 0, 'visibility' => 'specific']);
        $plan->instances()->attach($instance->id);

        $plans = $this->manager->forInstance($instance->id);

        $this->assertCount(1, $plans);
        $this->assertSame('VIP Plan', $plans->first()->name);
    }

    public function test_for_instance_excludes_specific_plans_not_assigned(): void
    {
        $instanceA = Instance::create(['name' => 'A', 'slug' => 'a', 'is_active' => true]);
        $instanceB = Instance::create(['name' => 'B', 'slug' => 'b', 'is_active' => true]);

        $plan = $this->manager->create(['name' => 'Only A', 'price_monthly' => 30, 'trial_days' => 0, 'visibility' => 'specific']);
        $plan->instances()->attach($instanceA->id);

        $plansB = $this->manager->forInstance($instanceB->id);
        $this->assertCount(0, $plansB);

        $plansA = $this->manager->forInstance($instanceA->id);
        $this->assertCount(1, $plansA);
    }

    public function test_for_instance_excludes_inactive_plans(): void
    {
        $instance = $this->makeRootInstance();

        $this->manager->create(['name' => 'Active', 'price_monthly' => 10, 'trial_days' => 0, 'visibility' => 'all', 'is_active' => true]);
        $this->manager->create(['name' => 'Inactive', 'price_monthly' => 10, 'trial_days' => 0, 'visibility' => 'all', 'is_active' => false]);

        $plans = $this->manager->forInstance($instance->id);

        $this->assertCount(1, $plans);
        $this->assertSame('Active', $plans->first()->name);
    }
}
