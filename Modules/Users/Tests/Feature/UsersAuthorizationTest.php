<?php

namespace Modules\Users\Tests\Feature;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\TeamContext;
use Modules\Users\Tests\TestCase;
use Spatie\Permission\Models\Permission;

final class UsersAuthorizationTest extends TestCase
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

    private function makeUser(string $email = 'user@test.com'): User
    {
        return User::create([
            'full_name' => 'User',
            'email' => $email,
            'password' => 'password',
        ]);
    }

    private function addActiveMembership(User $user, Instance $instance): void
    {
        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instance->id,
            'user_id' => $user->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function grantPermission(User $user, Instance $instance, string $permission): void
    {
        TeamContext::set($instance->id);
        Permission::findOrCreate($permission);
        $user->givePermissionTo($permission);
    }

    public function test_users_index_requires_users_view_permission(): void
    {
        $instance = $this->makeInstance();
        $user = $this->makeUser();
        $this->addActiveMembership($user, $instance);

        $this->actingAs($user)
            ->get("/i/{$instance->slug}/users")
            ->assertStatus(403);
    }

    public function test_users_index_allows_users_view_permission(): void
    {
        $instance = $this->makeInstance('view');
        $user = $this->makeUser('view@test.com');
        $this->addActiveMembership($user, $instance);
        $this->grantPermission($user, $instance, 'users.view');

        $this->actingAs($user)
            ->get("/i/{$instance->slug}/users")
            ->assertOk();
    }

    public function test_users_create_requires_users_manage_permission(): void
    {
        $instance = $this->makeInstance('manage');
        $user = $this->makeUser('manage@test.com');
        $this->addActiveMembership($user, $instance);
        $this->grantPermission($user, $instance, 'users.view');

        $this->actingAs($user)
            ->get("/i/{$instance->slug}/users/create")
            ->assertStatus(403);
    }

    public function test_users_create_allows_users_manage_permission(): void
    {
        $instance = $this->makeInstance('manage2');
        $user = $this->makeUser('manage2@test.com');
        $this->addActiveMembership($user, $instance);
        $this->grantPermission($user, $instance, 'users.manage');

        $this->actingAs($user)
            ->get("/i/{$instance->slug}/users/create")
            ->assertOk();
    }

    public function test_users_index_scopes_to_current_instance(): void
    {
        $instanceA = $this->makeInstance('alpha');
        $instanceB = $this->makeInstance('beta');

        $actor = $this->makeUser('actor@test.com');
        $userA = $this->makeUser('a@test.com');
        $userB = $this->makeUser('b@test.com');

        $this->addActiveMembership($actor, $instanceA);
        $this->addActiveMembership($userA, $instanceA);
        $this->addActiveMembership($userB, $instanceB);

        $this->grantPermission($actor, $instanceA, 'users.view');

        $response = $this->actingAs($actor)->get("/i/{$instanceA->slug}/users");
        $response->assertOk();

        $users = $response->viewData('users');
        $ids = $users->pluck('id')->all();

        $this->assertContains($actor->id, $ids);
        $this->assertContains($userA->id, $ids);
        $this->assertNotContains($userB->id, $ids);
    }
}
