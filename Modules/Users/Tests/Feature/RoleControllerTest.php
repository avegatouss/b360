<?php

namespace Modules\Users\Tests\Feature;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Core\Hooks\DTO\PermissionGroup;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Support\TeamContext;
use Modules\Core\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class RoleControllerTest extends TestCase
{
    private Instance $root;
    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.installed' => true]);

        $this->root = Instance::create([
            'name' => 'Root',
            'slug' => 'root',
            'is_active' => true,
            'meta' => ['is_root' => true],
        ]);

        $this->superAdmin = User::create([
            'full_name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        TeamContext::clear();
        Permission::firstOrCreate(['name' => 'users.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'users.manage', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $this->superAdmin->assignRole('super-admin');

        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $this->root->id,
            'user_id' => $this->superAdmin->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Register test permission groups
        $registry = app(HookRegistry::class);
        $registry->addPermissionGroup(new PermissionGroup(
            id: 'dashboard',
            label: 'Tableau de bord',
            permissions: ['dashboard.view' => 'Voir le tableau de bord'],
            priority: 1000,
            module: 'Dashboard',
        ));
        $registry->addPermissionGroup(new PermissionGroup(
            id: 'users',
            label: 'Utilisateurs',
            permissions: [
                'users.view' => 'Voir les utilisateurs',
                'users.manage' => 'Gerer les utilisateurs',
            ],
            priority: 900,
            module: 'Users',
        ));
    }

    public function test_index_lists_roles(): void
    {
        Role::firstOrCreate(['name' => 'instance-admin', 'guard_name' => 'web']);

        $this->actingAs($this->superAdmin)
            ->get("/i/{$this->root->slug}/roles")
            ->assertOk()
            ->assertSee('super-admin')
            ->assertSee('instance-admin');
    }

    public function test_index_shows_permission_groups(): void
    {
        $this->actingAs($this->superAdmin)
            ->get("/i/{$this->root->slug}/roles")
            ->assertOk()
            // The view renders the permission group LABELS (not the keys) inside
            // the "Référence des permissions" panel.
            ->assertSee('Tableau de bord')
            ->assertSee('Utilisateurs')
            ->assertSee('Voir le tableau de bord');
    }

    public function test_create_page_loads(): void
    {
        $this->actingAs($this->superAdmin)
            ->get("/i/{$this->root->slug}/roles/create")
            ->assertOk()
            // The view renders 'Nouveau rôle' (with the French circumflex).
            ->assertSee('Nouveau rôle', escape: false);
    }

    public function test_store_creates_role(): void
    {
        Permission::firstOrCreate(['name' => 'dashboard.view', 'guard_name' => 'web']);

        $this->actingAs($this->superAdmin)
            ->post("/i/{$this->root->slug}/roles", [
                'name' => 'comptable',
                'permissions' => ['dashboard.view', 'users.view'],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('roles', ['name' => 'comptable']);

        $role = Role::where('name', 'comptable')->first();
        $this->assertTrue($role->hasPermissionTo('dashboard.view'));
        $this->assertTrue($role->hasPermissionTo('users.view'));
    }

    public function test_store_validates_unique_name(): void
    {
        Role::firstOrCreate(['name' => 'existing', 'guard_name' => 'web']);

        $this->actingAs($this->superAdmin)
            ->post("/i/{$this->root->slug}/roles", [
                'name' => 'existing',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_edit_page_loads_with_permissions(): void
    {
        Permission::firstOrCreate(['name' => 'dashboard.view', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $role->givePermissionTo('dashboard.view');

        $this->actingAs($this->superAdmin)
            ->get("/i/{$this->root->slug}/roles/{$role->id}/edit")
            ->assertOk()
            // The view renders ucfirst($role->name) → 'Editor' in the page title.
            ->assertSee('Editor');
    }

    public function test_update_syncs_permissions(): void
    {
        Permission::firstOrCreate(['name' => 'dashboard.view', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

        $this->actingAs($this->superAdmin)
            ->put("/i/{$this->root->slug}/roles/{$role->id}", [
                'permissions' => ['users.view', 'users.manage'],
            ])
            ->assertRedirect();

        $role->refresh();
        $perms = $role->permissions->pluck('name')->toArray();
        $this->assertContains('users.view', $perms);
        $this->assertContains('users.manage', $perms);
    }

    public function test_update_super_admin_is_rejected(): void
    {
        $role = Role::where('name', 'super-admin')->first();

        $this->actingAs($this->superAdmin)
            ->put("/i/{$this->root->slug}/roles/{$role->id}", [
                'permissions' => ['users.view'],
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_destroy_deletes_unused_role(): void
    {
        $role = Role::firstOrCreate(['name' => 'disposable', 'guard_name' => 'web']);

        $this->actingAs($this->superAdmin)
            ->delete("/i/{$this->root->slug}/roles/{$role->id}")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('roles', ['name' => 'disposable']);
    }

    public function test_destroy_rejects_role_with_users(): void
    {
        $role = Role::firstOrCreate(['name' => 'in-use', 'guard_name' => 'web']);
        $user = User::create(['full_name' => 'U', 'email' => 'u@test.com', 'password' => 'pwd']);
        TeamContext::clear();
        $user->assignRole('in-use');

        $this->actingAs($this->superAdmin)
            ->delete("/i/{$this->root->slug}/roles/{$role->id}")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['name' => 'in-use']);
    }

    public function test_roles_require_users_manage_permission(): void
    {
        $nonAdmin = User::create([
            'full_name' => 'Basic',
            'email' => 'basic@test.com',
            'password' => 'password',
        ]);

        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $this->root->id,
            'user_id' => $nonAdmin->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($nonAdmin)
            ->get("/i/{$this->root->slug}/roles")
            ->assertForbidden();
    }

    public function test_non_root_instance_can_manage_roles(): void
    {
        $instance = Instance::create(['name' => 'Client', 'slug' => 'client', 'is_active' => true]);

        DB::connection('system')->table('instance_user')->insert([
            'instance_id' => $instance->id,
            'user_id' => $this->superAdmin->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->superAdmin)
            ->get("/i/{$instance->slug}/roles")
            ->assertOk();
    }
}
