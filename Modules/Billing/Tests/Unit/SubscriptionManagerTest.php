<?php

namespace Modules\Billing\Tests\Unit;

use Carbon\Carbon;
use Modules\Billing\Models\Plan;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Services\SubscriptionManager;
use Modules\Billing\Tests\TestCase;

final class SubscriptionManagerTest extends TestCase
{
    private SubscriptionManager $manager;
    private Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = app(SubscriptionManager::class);
        $this->plan = Plan::create([
            'name' => 'Test Plan',
            'slug' => 'test-plan',
            'price_monthly' => 19.99,
            'trial_days' => 14,
        ]);
    }

    public function test_subscribe_creates_trial(): void
    {
        $sub = $this->manager->subscribe(1, $this->plan->id);

        $this->assertSame('trial', $sub->status);
        $this->assertNotNull($sub->trial_ends_at);
        $this->assertTrue($sub->trial_ends_at->isAfter(now()->addDays(13)));
        $this->assertDatabaseHas('subscriptions', [
            'instance_id' => 1,
            'plan_id' => $this->plan->id,
            'status' => 'trial',
        ]);
    }

    public function test_subscribe_with_zero_trial(): void
    {
        $sub = $this->manager->subscribe(1, $this->plan->id, trialDays: 0);

        $this->assertSame('active', $sub->status);
        $this->assertNull($sub->trial_ends_at);
    }

    public function test_subscribe_uses_instance_override(): void
    {
        // Set instance-specific trial days override via DB
        $this->setSettingDirectly('billing', 'trial_days_override', '5', 1);

        $sub = $this->manager->subscribe(1, $this->plan->id);

        $this->assertSame('trial', $sub->status);
        $expectedEnd = now()->addDays(5);
        $this->assertTrue(
            $sub->trial_ends_at->diffInMinutes($expectedEnd) < 2,
            "Trial should end in ~5 days"
        );
    }

    public function test_current_returns_latest(): void
    {
        Subscription::create([
            'instance_id' => 1,
            'plan_id' => $this->plan->id,
            'status' => 'expired',
            'starts_at' => now()->subMonth(),
        ]);

        $active = $this->manager->subscribe(1, $this->plan->id, trialDays: 0);

        $current = $this->manager->current(1);
        $this->assertNotNull($current);
        $this->assertSame($active->id, $current->id);
    }

    public function test_is_active_true_for_trial(): void
    {
        $this->manager->subscribe(1, $this->plan->id);

        $this->assertTrue($this->manager->isActive(1));
    }

    public function test_is_active_true_for_active(): void
    {
        $this->manager->subscribe(1, $this->plan->id, trialDays: 0);

        $this->assertTrue($this->manager->isActive(1));
    }

    public function test_is_active_false_for_expired(): void
    {
        Subscription::create([
            'instance_id' => 2,
            'plan_id' => $this->plan->id,
            'status' => 'expired',
            'starts_at' => now()->subMonth(),
        ]);

        $this->assertFalse($this->manager->isActive(2));
    }

    public function test_is_active_false_for_cancelled(): void
    {
        Subscription::create([
            'instance_id' => 3,
            'plan_id' => $this->plan->id,
            'status' => 'cancelled',
            'starts_at' => now()->subMonth(),
            'cancelled_at' => now(),
        ]);

        $this->assertFalse($this->manager->isActive(3));
    }

    public function test_cancel_sets_status_and_reason(): void
    {
        $sub = $this->manager->subscribe(1, $this->plan->id, trialDays: 0);

        $cancelled = $this->manager->cancel($sub, 'Too expensive');

        $this->assertSame('cancelled', $cancelled->status);
        $this->assertNotNull($cancelled->cancelled_at);
        $this->assertSame('Too expensive', $cancelled->cancellation_reason);
    }

    public function test_renew_extends_end_date(): void
    {
        $sub = $this->manager->subscribe(1, $this->plan->id, trialDays: 0);
        $endsAt = Carbon::parse('2027-01-01');

        $renewed = $this->manager->renew($sub, $endsAt);

        $this->assertSame('active', $renewed->status);
        $this->assertTrue($renewed->ends_at->eq($endsAt));
    }

    public function test_change_plan_updates_plan_id(): void
    {
        $sub = $this->manager->subscribe(1, $this->plan->id, trialDays: 0);

        $newPlan = Plan::create([
            'name' => 'Premium',
            'slug' => 'premium',
            'price_monthly' => 49.99,
            'trial_days' => 0,
        ]);

        $changed = $this->manager->changePlan($sub, $newPlan->id);
        $this->assertSame($newPlan->id, $changed->plan_id);
    }

    public function test_expire_overdue_marks_expired(): void
    {
        // Create overdue trial
        Subscription::create([
            'instance_id' => 10,
            'plan_id' => $this->plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->subDay(),
            'starts_at' => now()->subMonth(),
        ]);

        // Create overdue active
        Subscription::create([
            'instance_id' => 11,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'ends_at' => now()->subDay(),
            'starts_at' => now()->subMonth(),
        ]);

        $count = $this->manager->expireOverdue();

        $this->assertSame(2, $count);
        $this->assertDatabaseHas('subscriptions', ['instance_id' => 10, 'status' => 'expired']);
        $this->assertDatabaseHas('subscriptions', ['instance_id' => 11, 'status' => 'expired']);
    }

    private function setSettingDirectly(string $group, string $key, string $value, int $instanceId): void
    {
        \Illuminate\Support\Facades\DB::connection('system')->table('settings')->insert([
            'instance_id' => $instanceId,
            'group' => $group,
            'key' => $key,
            'value' => $value,
            'type' => 'integer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
