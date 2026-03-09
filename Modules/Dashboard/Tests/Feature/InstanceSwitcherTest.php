<?php

namespace Modules\Dashboard\Tests\Feature;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\TeamContext;
use Modules\Dashboard\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class InstanceSwitcherTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.installed' => true]);
    }

    private function makeRootInstance(): Instance
    {
        return Instance::create([
            'name' => 'Root',
            'slug' => 'root',
            'is_active' => true,
            'meta' => ['is_root' => true],
        ]);
    }

    private function makeInstance(string $slug, string $name): Instance
    {
        return Instance::create([
            'name' => $name,
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    private function makeUser(string $email = 'user@test.com'): User
    {
        return User::create([
            'full_name' => 'User',
            'email' => $email,
            'password' => 'password',
        ]);
    }

    private function makeSuperAdmin(Instance $instance): User
    {
        $user = $this->makeUser('admin@test.com');

        TeamContext::clear();
        Role::findOrCreate('super-admin');
        $user->assignRole('super-admin');

        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instance->id,
            'user_id' => $user->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    private function addMembership(User $user, Instance $instance): void
    {
        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instance->id,
            'user_id' => $user->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_super_admin_sees_instance_switcher_dropdown(): void
    {
        $root = $this->makeRootInstance();
        $clientA = $this->makeInstance('client-a', 'Client A');
        $user = $this->makeSuperAdmin($root);

        $response = $this->actingAs($user)
            ->get("/i/{$root->slug}");

        $response->assertOk();
        // Should see both instances in the dropdown
        $response->assertSee('Client A');
        $response->assertSee('Root');
        // Should see the dropdown toggle chevron
        $response->assertSee('ti-chevron-down');
    }

    public function test_super_admin_sees_root_badge_on_root_instance(): void
    {
        $root = $this->makeRootInstance();
        $clientA = $this->makeInstance('client-a', 'Client A');
        $user = $this->makeSuperAdmin($root);

        $response = $this->actingAs($user)
            ->get("/i/{$root->slug}");

        $response->assertOk();
        // Root badge should appear
        $response->assertSee('badge bg-danger');
    }

    public function test_regular_user_does_not_see_switcher_dropdown(): void
    {
        $instance = $this->makeInstance('acme', 'Acme Corp');
        $user = $this->makeUser('regular@test.com');

        $this->addMembership($user, $instance);

        $response = $this->actingAs($user)
            ->get("/i/{$instance->slug}");

        $response->assertOk();
        // Should see static instance name but NOT the dropdown chevron
        $response->assertSee('Acme Corp');
        $response->assertDontSee('ti-chevron-down');
    }

    public function test_switcher_links_navigate_to_correct_instances(): void
    {
        $root = $this->makeRootInstance();
        $clientA = $this->makeInstance('client-a', 'Client A');
        $clientB = $this->makeInstance('client-b', 'Client B');
        $user = $this->makeSuperAdmin($root);

        $response = $this->actingAs($user)
            ->get("/i/{$root->slug}");

        $response->assertOk();
        // Links to each instance's dashboard
        $response->assertSee(route('dashboard.instance', 'client-a'));
        $response->assertSee(route('dashboard.instance', 'client-b'));
        $response->assertSee(route('dashboard.instance', 'root'));
    }

    public function test_switcher_only_shows_active_instances(): void
    {
        $root = $this->makeRootInstance();
        $active = $this->makeInstance('active-co', 'Active Co');
        $inactive = Instance::create([
            'name' => 'Inactive Co',
            'slug' => 'inactive-co',
            'is_active' => false,
        ]);
        $user = $this->makeSuperAdmin($root);

        $response = $this->actingAs($user)
            ->get("/i/{$root->slug}");

        $response->assertOk();
        $response->assertSee('Active Co');
        $response->assertDontSee('Inactive Co');
    }
}
