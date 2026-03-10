<?php

namespace Modules\Instances\Tests\Feature;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\TeamContext;
use Modules\Instances\Tests\TestCase;
use Modules\Settings\Services\SettingsManager;
use Spatie\Permission\Models\Role;

final class InstancesManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.installed' => true]);
        config(['app.instance_db_strategy' => 'shared']);
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

    private function makeInstance(string $slug = 'acme'): Instance
    {
        return Instance::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    private function makeUser(string $email = 'admin@test.com'): User
    {
        return User::create([
            'full_name' => 'Admin',
            'email' => $email,
            'password' => 'password',
        ]);
    }

    private function addMembership(User $user, Instance $instance, string $status = 'active'): void
    {
        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instance->id,
            'user_id' => $user->id,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeRootSuperAdmin(Instance $root): User
    {
        $user = $this->makeUser();

        TeamContext::clear();
        Role::findOrCreate('super-admin');
        $user->assignRole('super-admin');

        TeamContext::set($root->id);
        Role::findOrCreate('super-admin');
        $user->assignRole('super-admin');

        $this->addMembership($user, $root, 'active');

        return $user;
    }

    private function allowInstanceCreation(): void
    {
        app(SettingsManager::class)->set('instances.allow_creation', true, 0, 'boolean');
    }

    public function test_index_is_accessible_for_root_super_admin(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $this->actingAs($user)
            ->get("/i/{$root->slug}/instances")
            ->assertOk();
    }

    public function test_create_is_blocked_when_setting_disabled(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $this->actingAs($user)
            ->get("/i/{$root->slug}/instances/create")
            ->assertStatus(403);
    }

    public function test_create_is_allowed_when_setting_enabled(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);
        $this->allowInstanceCreation();

        $this->actingAs($user)
            ->get("/i/{$root->slug}/instances/create")
            ->assertOk();
    }

    public function test_store_creates_instance(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);
        $this->allowInstanceCreation();

        $payload = [
            'name' => 'New Instance',
            'slug' => 'new-instance',
            'domain' => null,
            'subdomain' => null,
            'database' => null,
            'is_active' => true,
        ];

        $this->actingAs($user)
            ->post("/i/{$root->slug}/instances", $payload)
            ->assertStatus(302);

        $this->assertDatabaseHas('instances', [
            'slug' => 'new-instance',
            'name' => 'New Instance',
        ]);
    }

    public function test_update_changes_instance_fields(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $instance = $this->makeInstance('update-me');

        $this->actingAs($user)
            ->put("/i/{$root->slug}/instances/{$instance->uuid}", [
                'name' => 'Updated Name',
                'domain' => 'example.com',
                'subdomain' => 'sub.example.com',
                'is_active' => true,
            ])
            ->assertStatus(302);

        $instance->refresh();
        $this->assertSame('Updated Name', $instance->name);
        $this->assertSame('example.com', $instance->domain);
        $this->assertSame('sub.example.com', $instance->subdomain);
    }

    public function test_toggle_flips_active(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);
        $instance = $this->makeInstance('toggle-me');

        $this->assertTrue($instance->is_active);

        $this->actingAs($user)
            ->put("/i/{$root->slug}/instances/{$instance->uuid}/toggle")
            ->assertStatus(302);

        $instance->refresh();
        $this->assertFalse($instance->is_active);
    }

    public function test_toggle_blocks_root_instance(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $this->actingAs($user)
            ->put("/i/{$root->slug}/instances/{$root->uuid}/toggle")
            ->assertStatus(403);
    }

    public function test_destroy_deletes_instance_and_memberships_and_roles(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);
        $instance = $this->makeInstance('delete-me');

        $member = $this->makeUser('member@test.com');
        $this->addMembership($member, $instance, 'active');

        TeamContext::set($instance->id);
        Role::findOrCreate('instance-admin');
        $member->assignRole('instance-admin');

        $this->actingAs($user)
            ->delete("/i/{$root->slug}/instances/{$instance->uuid}")
            ->assertStatus(302);

        $this->assertDatabaseMissing('instances', ['id' => $instance->id]);
        $this->assertDatabaseMissing('instance_user', ['instance_id' => $instance->id]);
        $this->assertSame(
            0,
            DB::connection('system')->table('model_has_roles')
                ->where('instance_id', $instance->id)
                ->count()
        );
    }

    public function test_destroy_blocks_root_instance(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $this->actingAs($user)
            ->delete("/i/{$root->slug}/instances/{$root->uuid}")
            ->assertStatus(403);
    }
}
