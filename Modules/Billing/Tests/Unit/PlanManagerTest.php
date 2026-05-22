<?php

namespace Modules\Billing\Tests\Unit;

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
}
