<?php

namespace Modules\Billing\Tests\Feature;

use App\Instances\Instance;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\Plan;
use Modules\Billing\Tests\TestCase;

final class PlanControllerTest extends TestCase
{
    public function test_index_lists_plans(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        Plan::create(['name' => 'Starter', 'slug' => 'starter', 'price_monthly' => 10, 'trial_days' => 14]);

        $this->actingAs($user)
            ->get("/i/{$root->slug}/billing/plans")
            ->assertOk()
            ->assertSee('Starter');
    }

    public function test_store_creates_plan(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $this->actingAs($user)
            ->post("/i/{$root->slug}/billing/plans", [
                'name' => 'Pro',
                'price_monthly' => 29.99,
                'trial_days' => 7,
                'visibility' => 'all',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('plans', ['name' => 'Pro']);
    }

    public function test_store_requires_superadmin(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeUser('basic@test.com');

        // Add user to instance but without super-admin role
        \Illuminate\Support\Facades\DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $root->id,
            'user_id' => $user->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->post("/i/{$root->slug}/billing/plans", [
                'name' => 'Hack',
                'price_monthly' => 0,
                'trial_days' => 0,
                'visibility' => 'all',
            ])
            ->assertStatus(403);
    }

    public function test_destroy_plan(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $plan = Plan::create(['name' => 'Temp', 'slug' => 'temp', 'price_monthly' => 5, 'trial_days' => 0]);

        $this->actingAs($user)
            ->delete("/i/{$root->slug}/billing/plans/{$plan->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('plans', ['name' => 'Temp']);
    }

    public function test_store_creates_plan_with_visibility_all(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $this->actingAs($user)
            ->post("/i/{$root->slug}/billing/plans", [
                'name' => 'Global Plan',
                'price_monthly' => 15,
                'trial_days' => 7,
                'visibility' => 'all',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('plans', [
            'name' => 'Global Plan',
            'visibility' => 'all',
        ]);
    }

    public function test_store_creates_plan_with_visibility_specific_and_assigns_instances(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $clientA = Instance::create(['name' => 'Client A', 'slug' => 'client-a', 'is_active' => true]);
        $clientB = Instance::create(['name' => 'Client B', 'slug' => 'client-b', 'is_active' => true]);

        $this->actingAs($user)
            ->post("/i/{$root->slug}/billing/plans", [
                'name' => 'VIP Plan',
                'price_monthly' => 50,
                'trial_days' => 0,
                'visibility' => 'specific',
                'instance_ids' => [$clientA->id, $clientB->id],
            ])
            ->assertRedirect();

        $plan = Plan::where('name', 'VIP Plan')->first();
        $this->assertNotNull($plan);
        $this->assertSame('specific', $plan->visibility);
        $this->assertCount(2, $plan->instances);
        $this->assertTrue($plan->instances->contains('id', $clientA->id));
        $this->assertTrue($plan->instances->contains('id', $clientB->id));
    }

    public function test_update_changes_visibility_and_syncs_instances(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $clientA = Instance::create(['name' => 'Client A', 'slug' => 'client-a', 'is_active' => true]);

        $plan = Plan::create([
            'name' => 'Evolving',
            'slug' => 'evolving',
            'price_monthly' => 20,
            'trial_days' => 0,
            'visibility' => 'all',
        ]);

        // Update to specific with one instance
        $this->actingAs($user)
            ->put("/i/{$root->slug}/billing/plans/{$plan->id}", [
                'name' => 'Evolving',
                'price_monthly' => 20,
                'trial_days' => 0,
                'visibility' => 'specific',
                'instance_ids' => [$clientA->id],
            ])
            ->assertRedirect();

        $plan->refresh();
        $this->assertSame('specific', $plan->visibility);
        $this->assertCount(1, $plan->instances);
        $this->assertTrue($plan->instances->contains('id', $clientA->id));
    }

    public function test_update_to_all_detaches_instances(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $clientA = Instance::create(['name' => 'Client A', 'slug' => 'client-a', 'is_active' => true]);

        $plan = Plan::create([
            'name' => 'Was Specific',
            'slug' => 'was-specific',
            'price_monthly' => 30,
            'trial_days' => 0,
            'visibility' => 'specific',
        ]);
        $plan->instances()->attach($clientA->id);

        // Update back to all
        $this->actingAs($user)
            ->put("/i/{$root->slug}/billing/plans/{$plan->id}", [
                'name' => 'Was Specific',
                'price_monthly' => 30,
                'trial_days' => 0,
                'visibility' => 'all',
            ])
            ->assertRedirect();

        $plan->refresh();
        $this->assertSame('all', $plan->visibility);
        $this->assertCount(0, $plan->instances);
    }

    public function test_index_shows_visibility_badge(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        Plan::create(['name' => 'All Plan', 'slug' => 'all-plan', 'price_monthly' => 10, 'trial_days' => 0, 'visibility' => 'all']);
        Plan::create(['name' => 'Specific Plan', 'slug' => 'specific-plan', 'price_monthly' => 20, 'trial_days' => 0, 'visibility' => 'specific']);

        $this->actingAs($user)
            ->get("/i/{$root->slug}/billing/plans")
            ->assertOk()
            ->assertSee('Toutes')
            ->assertSee('0 instance(s)');
    }
}
