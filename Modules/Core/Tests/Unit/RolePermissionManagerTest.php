<?php

namespace Modules\Core\Tests\Unit;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Core\Hooks\DTO\PermissionGroup;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Services\RolePermissionManager;
use Modules\Core\Support\TeamContext;
use Modules\Core\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class RolePermissionManagerTest extends TestCase
{
    private RolePermissionManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = app(RolePermissionManager::class);
    }

    // ─── Roles ───────────────────────────────────────────────

    public function test_create_role_persists_in_database(): void
    {
        $role = $this->manager->createRole('editor');

        $this->assertDatabaseHas('roles', ['name' => 'editor', 'guard_name' => 'web']);
        $this->assertSame('editor', $role->name);
    }

    public function test_create_role_with_permissions(): void
    {
        Permission::firstOrCreate(['name' => 'posts.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'posts.manage', 'guard_name' => 'web']);

        $role = $this->manager->createRole('editor', ['posts.view', 'posts.manage']);

        $this->assertCount(2, $role->permissions);
        $this->assertTrue($role->hasPermissionTo('posts.view'));
        $this->assertTrue($role->hasPermissionTo('posts.manage'));
    }

    public function test_update_role_syncs_permissions(): void
    {
        Permission::firstOrCreate(['name' => 'a.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'b.view', 'guard_name' => 'web']);

        $role = $this->manager->createRole('tester', ['a.view']);

        $this->manager->updateRole($role, ['b.view']);
        $role->refresh();

        $perms = $role->permissions->pluck('name')->toArray();
        $this->assertContains('b.view', $perms);
        $this->assertNotContains('a.view', $perms);
    }

    public function test_delete_role_succeeds_when_no_users(): void
    {
        $role = $this->manager->createRole('temp');

        $this->assertTrue($this->manager->deleteRole($role));
        $this->assertDatabaseMissing('roles', ['name' => 'temp']);
    }

    public function test_delete_super_admin_is_forbidden(): void
    {
        $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

        $this->assertFalse($this->manager->deleteRole($role));
        $this->assertDatabaseHas('roles', ['name' => 'super-admin']);
    }

    public function test_delete_role_fails_when_users_assigned(): void
    {
        $role = $this->manager->createRole('busy-role');
        $user = User::create(['full_name' => 'Test', 'email' => 'test@x.com', 'password' => 'pwd']);

        TeamContext::clear();
        $user->assignRole('busy-role');

        $this->assertFalse($this->manager->deleteRole($role));
    }

    public function test_assignable_roles_excludes_super_admin(): void
    {
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $this->manager->createRole('instance-admin');
        $this->manager->createRole('manager');

        $roles = $this->manager->assignableRoles();

        $names = $roles->pluck('name')->toArray();
        $this->assertNotContains('super-admin', $names);
        $this->assertContains('instance-admin', $names);
        $this->assertContains('manager', $names);
    }

    public function test_users_with_role_counts_correctly(): void
    {
        $role = $this->manager->createRole('counted');
        $this->assertSame(0, $this->manager->usersWithRole($role));

        $user = User::create(['full_name' => 'A', 'email' => 'a@x.com', 'password' => 'pwd']);
        TeamContext::clear();
        $user->assignRole('counted');

        $this->assertSame(1, $this->manager->usersWithRole($role));
    }

    // ─── Permission Groups (hooks) ──────────────────────────

    public function test_permission_groups_returns_registered_groups(): void
    {
        $registry = app(HookRegistry::class);
        $registry->addPermissionGroup(new PermissionGroup(
            id: 'test-mod',
            label: 'Test Module',
            permissions: ['test.view' => 'Voir', 'test.manage' => 'Gerer'],
            priority: 500,
            module: 'TestMod',
        ));

        $groups = $this->manager->permissionGroups();

        $this->assertTrue($groups->contains('id', 'test-mod'));
        $group = $groups->firstWhere('id', 'test-mod');
        $this->assertSame('Test Module', $group->label);
        $this->assertArrayHasKey('test.view', $group->permissions);
    }

    public function test_all_permission_names_collects_from_all_groups(): void
    {
        $registry = app(HookRegistry::class);

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'alpha',
            label: 'Alpha',
            permissions: ['alpha.view' => 'View', 'alpha.manage' => 'Manage'],
        ));
        $registry->addPermissionGroup(new PermissionGroup(
            id: 'beta',
            label: 'Beta',
            permissions: ['beta.edit' => 'Edit'],
        ));

        $names = $this->manager->allPermissionNames();

        $this->assertContains('alpha.view', $names);
        $this->assertContains('alpha.manage', $names);
        $this->assertContains('beta.edit', $names);
    }

    public function test_sync_registered_permissions_creates_in_db(): void
    {
        $registry = app(HookRegistry::class);
        $registry->addPermissionGroup(new PermissionGroup(
            id: 'sync-test',
            label: 'Sync',
            permissions: ['sync.one' => 'One', 'sync.two' => 'Two'],
        ));

        $count = $this->manager->syncRegisteredPermissions();

        $this->assertGreaterThanOrEqual(2, $count);
        $this->assertDatabaseHas('permissions', ['name' => 'sync.one']);
        $this->assertDatabaseHas('permissions', ['name' => 'sync.two']);
    }

    // ─── User role assignment ────────────────────────────────

    public function test_assign_role_to_user_in_instance(): void
    {
        $instance = Instance::create(['name' => 'Test', 'slug' => 'test', 'is_active' => true]);
        $user = User::create(['full_name' => 'U', 'email' => 'u@x.com', 'password' => 'pwd']);
        $this->manager->createRole('agent');

        $this->manager->assignRoleToUser($user, 'agent', $instance->id);

        $roleName = $this->manager->userRoleInInstance($user, $instance->id);
        $this->assertSame('agent', $roleName);
    }

    public function test_sync_user_role_replaces_existing(): void
    {
        $instance = Instance::create(['name' => 'Test', 'slug' => 'test', 'is_active' => true]);
        $user = User::create(['full_name' => 'U', 'email' => 'u@x.com', 'password' => 'pwd']);
        $this->manager->createRole('agent');
        $this->manager->createRole('manager');

        $this->manager->assignRoleToUser($user, 'agent', $instance->id);
        $this->manager->syncUserRole($user, 'manager', $instance->id);

        $roleName = $this->manager->userRoleInInstance($user, $instance->id);
        $this->assertSame('manager', $roleName);
    }

    public function test_user_role_in_instance_returns_null_when_none(): void
    {
        $user = User::create(['full_name' => 'U', 'email' => 'u@x.com', 'password' => 'pwd']);

        $this->assertNull($this->manager->userRoleInInstance($user, 999));
    }

    public function test_role_permissions_returns_assigned_permissions(): void
    {
        Permission::firstOrCreate(['name' => 'dash.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'users.view', 'guard_name' => 'web']);

        $role = $this->manager->createRole('viewer', ['dash.view', 'users.view']);

        $perms = $this->manager->rolePermissions($role);

        $this->assertContains('dash.view', $perms);
        $this->assertContains('users.view', $perms);
    }
}
