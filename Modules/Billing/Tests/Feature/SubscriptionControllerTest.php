<?php

namespace Modules\Billing\Tests\Feature;

use App\Instances\Instance;
use Illuminate\Support\Facades\DB;
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

    public function test_billing_index_shows_available_plans_when_no_subscription(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        Plan::create(['name' => 'Starter', 'slug' => 'starter', 'price_monthly' => 10, 'trial_days' => 14, 'visibility' => 'all']);
        Plan::create(['name' => 'Pro', 'slug' => 'pro', 'price_monthly' => 30, 'trial_days' => 7, 'visibility' => 'all']);

        $this->actingAs($user)
            ->get("/i/{$root->slug}/billing")
            ->assertOk()
            ->assertSee('Plans disponibles')
            ->assertSee('Starter')
            ->assertSee('Pro')
            ->assertSee('Choisir ce plan');
    }

    public function test_billing_index_hides_plans_section_when_subscription_active(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $plan = Plan::create(['name' => 'Pro', 'slug' => 'pro', 'price_monthly' => 20, 'trial_days' => 0, 'visibility' => 'all']);
        Subscription::create([
            'instance_id' => $root->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
        ]);

        $this->actingAs($user)
            ->get("/i/{$root->slug}/billing")
            ->assertOk()
            ->assertDontSee('Plans disponibles');
    }

    public function test_billing_index_only_shows_visible_plans_for_instance(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        // Plan visible to all
        Plan::create(['name' => 'Global', 'slug' => 'global', 'price_monthly' => 10, 'trial_days' => 0, 'visibility' => 'all']);

        // Plan specific to another instance (not root)
        $other = Instance::create(['name' => 'Other', 'slug' => 'other', 'is_active' => true]);
        $specific = Plan::create(['name' => 'Other Only', 'slug' => 'other-only', 'price_monthly' => 50, 'trial_days' => 0, 'visibility' => 'specific']);
        $specific->instances()->attach($other->id);

        $this->actingAs($user)
            ->get("/i/{$root->slug}/billing")
            ->assertOk()
            ->assertSee('Global')
            ->assertDontSee('Other Only');
    }

    public function test_billing_index_shows_specific_plan_for_assigned_instance(): void
    {
        // Create a non-root instance with a user
        $instance = Instance::create(['name' => 'Client X', 'slug' => 'client-x', 'is_active' => true]);

        $user = $this->makeUser('client@test.com');
        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instance->id,
            'user_id' => $user->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Plan specifically for this instance
        $vip = Plan::create(['name' => 'VIP Client X', 'slug' => 'vip-cx', 'price_monthly' => 100, 'trial_days' => 0, 'visibility' => 'specific']);
        $vip->instances()->attach($instance->id);

        // Plan for another instance
        $other = Instance::create(['name' => 'Other', 'slug' => 'other', 'is_active' => true]);
        $excluded = Plan::create(['name' => 'Not For X', 'slug' => 'not-for-x', 'price_monthly' => 50, 'trial_days' => 0, 'visibility' => 'specific']);
        $excluded->instances()->attach($other->id);

        $this->actingAs($user)
            ->get("/i/{$instance->slug}/billing")
            ->assertOk()
            ->assertSee('VIP Client X')
            ->assertDontSee('Not For X');
    }
}
