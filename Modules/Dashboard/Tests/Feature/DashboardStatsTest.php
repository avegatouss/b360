<?php

namespace Modules\Dashboard\Tests\Feature;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Dashboard\Tests\TestCase;

final class DashboardStatsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.installed' => true]);
    }

    private function makeInstance(string $slug = 'acme'): Instance
    {
        return Instance::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    private function makeUser(string $email): User
    {
        return User::create([
            'full_name' => 'User',
            'email' => $email,
            'password' => 'password',
        ]);
    }

    private function addMembership(User $user, Instance $instance, string $status): void
    {
        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instance->id,
            'user_id' => $user->id,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_dashboard_counts_members_correctly(): void
    {
        $instance = $this->makeInstance();

        $user1 = $this->makeUser('u1@test.com');
        $user2 = $this->makeUser('u2@test.com');
        $user3 = $this->makeUser('u3@test.com');

        $this->addMembership($user1, $instance, 'active');
        $this->addMembership($user2, $instance, 'active');
        $this->addMembership($user3, $instance, 'disabled');

        $response = $this->actingAs($user1)->get("/i/{$instance->slug}");
        $response->assertOk();

        $this->assertSame(2, $response->viewData('memberCount'));
        $this->assertSame(3, $response->viewData('totalUsers'));
    }
}
