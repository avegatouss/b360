<?php

namespace Modules\Billing\Tests\Feature;

use Modules\Billing\Models\Plan;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Tests\TestCase;

final class SubscriptionControllerTest extends TestCase
{
    public function test_subscribe_creates_subscription(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $plan = Plan::create(['name' => 'Pro', 'slug' => 'pro', 'price_monthly' => 20, 'trial_days' => 14]);

        $this->actingAs($user)
            ->post("/i/{$root->slug}/billing/subscribe/{$plan->id}")
            ->assertRedirect();

        $this->assertDatabaseHas('subscriptions', [
            'instance_id' => $root->id,
            'plan_id' => $plan->id,
        ]);
    }

    public function test_cancel_subscription(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $plan = Plan::create(['name' => 'Pro', 'slug' => 'pro', 'price_monthly' => 20, 'trial_days' => 0]);
        Subscription::create([
            'instance_id' => $root->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
        ]);

        $this->actingAs($user)
            ->delete("/i/{$root->slug}/billing/subscription")
            ->assertRedirect();

        $this->assertDatabaseHas('subscriptions', [
            'instance_id' => $root->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_billing_index_shows_current(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $this->actingAs($user)
            ->get("/i/{$root->slug}/billing")
            ->assertOk();
    }

    public function test_subscribe_requires_authentication(): void
    {
        $root = $this->makeRootInstance();

        $plan = Plan::create(['name' => 'Pro', 'slug' => 'pro', 'price_monthly' => 20, 'trial_days' => 0]);

        $this->post("/i/{$root->slug}/billing/subscribe/{$plan->id}")
            ->assertRedirect(); // redirects to login
    }
}
