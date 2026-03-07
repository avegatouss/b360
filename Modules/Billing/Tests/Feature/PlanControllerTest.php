<?php

namespace Modules\Billing\Tests\Feature;

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
}
