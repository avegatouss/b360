<?php

namespace Modules\Billing\Tests\Unit;

use App\Instances\Instance;
use Modules\Billing\Models\Plan;
use Modules\Billing\Tests\TestCase;

final class PlanVisibilityTest extends TestCase
{
    public function test_scope_visible_to_includes_all_visibility_plans(): void
    {
        $instance = Instance::create(['name' => 'Test', 'slug' => 'test', 'is_active' => true]);

        Plan::create(['name' => 'All Plan', 'slug' => 'all', 'price_monthly' => 10, 'trial_days' => 0, 'visibility' => 'all']);

        $plans = Plan::visibleTo($instance->id)->get();

        $this->assertCount(1, $plans);
        $this->assertSame('All Plan', $plans->first()->name);
    }

    public function test_scope_visible_to_includes_specific_plan_for_assigned_instance(): void
    {
        $instance = Instance::create(['name' => 'Assigned', 'slug' => 'assigned', 'is_active' => true]);

        $plan = Plan::create(['name' => 'Specific', 'slug' => 'specific', 'price_monthly' => 20, 'trial_days' => 0, 'visibility' => 'specific']);
        $plan->instances()->attach($instance->id);

        $plans = Plan::visibleTo($instance->id)->get();

        $this->assertCount(1, $plans);
        $this->assertSame('Specific', $plans->first()->name);
    }

    public function test_scope_visible_to_excludes_specific_plan_for_unassigned_instance(): void
    {
        $assigned = Instance::create(['name' => 'Assigned', 'slug' => 'assigned', 'is_active' => true]);
        $unassigned = Instance::create(['name' => 'Unassigned', 'slug' => 'unassigned', 'is_active' => true]);

        $plan = Plan::create(['name' => 'Exclusive', 'slug' => 'exclusive', 'price_monthly' => 50, 'trial_days' => 0, 'visibility' => 'specific']);
        $plan->instances()->attach($assigned->id);

        $plans = Plan::visibleTo($unassigned->id)->get();
        $this->assertCount(0, $plans);
    }

    public function test_scope_visible_to_combines_all_and_specific(): void
    {
        $instance = Instance::create(['name' => 'Combined', 'slug' => 'combined', 'is_active' => true]);

        Plan::create(['name' => 'Global', 'slug' => 'global', 'price_monthly' => 10, 'trial_days' => 0, 'visibility' => 'all']);

        $specific = Plan::create(['name' => 'Special', 'slug' => 'special', 'price_monthly' => 30, 'trial_days' => 0, 'visibility' => 'specific']);
        $specific->instances()->attach($instance->id);

        $otherSpecific = Plan::create(['name' => 'Not Mine', 'slug' => 'not-mine', 'price_monthly' => 40, 'trial_days' => 0, 'visibility' => 'specific']);
        // Not attached to this instance

        $plans = Plan::visibleTo($instance->id)->get();

        $this->assertCount(2, $plans);
        $names = $plans->pluck('name')->toArray();
        $this->assertContains('Global', $names);
        $this->assertContains('Special', $names);
        $this->assertNotContains('Not Mine', $names);
    }

    public function test_scope_visible_to_with_multiple_instances_on_one_plan(): void
    {
        $instanceA = Instance::create(['name' => 'A', 'slug' => 'a', 'is_active' => true]);
        $instanceB = Instance::create(['name' => 'B', 'slug' => 'b', 'is_active' => true]);
        $instanceC = Instance::create(['name' => 'C', 'slug' => 'c', 'is_active' => true]);

        $plan = Plan::create(['name' => 'Shared', 'slug' => 'shared', 'price_monthly' => 25, 'trial_days' => 0, 'visibility' => 'specific']);
        $plan->instances()->attach([$instanceA->id, $instanceB->id]);

        $this->assertCount(1, Plan::visibleTo($instanceA->id)->get());
        $this->assertCount(1, Plan::visibleTo($instanceB->id)->get());
        $this->assertCount(0, Plan::visibleTo($instanceC->id)->get());
    }

    public function test_plan_instances_relation_returns_attached_instances(): void
    {
        $instanceA = Instance::create(['name' => 'A', 'slug' => 'a', 'is_active' => true]);
        $instanceB = Instance::create(['name' => 'B', 'slug' => 'b', 'is_active' => true]);

        $plan = Plan::create(['name' => 'Multi', 'slug' => 'multi', 'price_monthly' => 15, 'trial_days' => 0, 'visibility' => 'specific']);
        $plan->instances()->attach([$instanceA->id, $instanceB->id]);

        $plan->refresh();
        $this->assertCount(2, $plan->instances);
        $this->assertTrue($plan->instances->contains('id', $instanceA->id));
        $this->assertTrue($plan->instances->contains('id', $instanceB->id));
    }

    public function test_plan_instances_sync_replaces_assignments(): void
    {
        $instanceA = Instance::create(['name' => 'A', 'slug' => 'a', 'is_active' => true]);
        $instanceB = Instance::create(['name' => 'B', 'slug' => 'b', 'is_active' => true]);

        $plan = Plan::create(['name' => 'Sync Test', 'slug' => 'sync', 'price_monthly' => 10, 'trial_days' => 0, 'visibility' => 'specific']);
        $plan->instances()->attach($instanceA->id);

        // Sync to B only
        $plan->instances()->sync([$instanceB->id]);
        $plan->refresh();

        $this->assertCount(1, $plan->instances);
        $this->assertTrue($plan->instances->contains('id', $instanceB->id));
        $this->assertFalse($plan->instances->contains('id', $instanceA->id));
    }

    public function test_plan_detach_removes_all_assignments(): void
    {
        $instanceA = Instance::create(['name' => 'A', 'slug' => 'a', 'is_active' => true]);

        $plan = Plan::create(['name' => 'Detach Test', 'slug' => 'detach', 'price_monthly' => 10, 'trial_days' => 0, 'visibility' => 'specific']);
        $plan->instances()->attach($instanceA->id);
        $this->assertCount(1, $plan->instances()->get());

        $plan->instances()->detach();
        $this->assertCount(0, $plan->instances()->get());
    }
}
