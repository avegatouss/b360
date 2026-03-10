<?php

namespace Modules\Core\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Hooks\Registry\HookRegistry;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RolePermissionManager
{
    public function __construct(
        private readonly HookRegistry $registry,
    ) {}

    // ─── Roles ───────────────────────────────────────────────

    /**
     * List roles for a given scope.
     * scope=0 → global template roles. scope=N → instance-specific roles.
     */
    public function roles(int $scopeId = 0): Collection
    {
        return Role::where('guard_name', 'web')->get();
    }

    /**
     * List roles excluding super-admin (for instance-level management).
     */
    public function assignableRoles(): Collection
    {
        return Role::where('guard_name', 'web')
            ->where('name', '!=', 'super-admin')
            ->orderBy('name')
            ->get();
    }

    public function createRole(string $name, array $permissionNames = [], int $scopeId = 0): Role
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($scopeId);

        $role = Role::create(['name' => $name, 'guard_name' => 'web']);

        if (!empty($permissionNames)) {
            $this->ensurePermissionsExist($permissionNames);
            $role->syncPermissions($permissionNames);
        }

        $registrar->setPermissionsTeamId(0);

        return $role;
    }

    public function updateRole(Role $role, array $permissionNames, int $scopeId = 0): Role
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($scopeId);

        $this->ensurePermissionsExist($permissionNames);
        $role->syncPermissions($permissionNames);

        $registrar->setPermissionsTeamId(0);
        $registrar->forgetCachedPermissions();

        return $role;
    }

    public function deleteRole(Role $role): bool
    {
        if ($role->name === 'super-admin') {
            return false;
        }

        // Check if any users are assigned this role
        $usersCount = DB::connection('system')
            ->table('model_has_roles')
            ->where('role_id', $role->id)
            ->count();

        if ($usersCount > 0) {
            return false;
        }

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return true;
    }

    // ─── Permissions ─────────────────────────────────────────

    /**
     * Get all registered permission groups from hooks.
     */
    public function permissionGroups(): Collection
    {
        return $this->registry->permissions();
    }

    /**
     * Get all permission names registered via hooks.
     */
    public function allPermissionNames(): array
    {
        $names = [];
        foreach ($this->permissionGroups() as $group) {
            if (isset($group->permissions) && is_array($group->permissions)) {
                $names = array_merge($names, array_keys($group->permissions));
            }
        }
        return array_unique($names);
    }

    /**
     * Get permissions currently assigned to a role (within a scope).
     */
    public function rolePermissions(Role $role, int $scopeId = 0): array
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($scopeId);

        $permissions = $role->permissions->pluck('name')->toArray();

        $registrar->setPermissionsTeamId(0);

        return $permissions;
    }

    /**
     * Ensure all permissions exist in DB (create if missing).
     */
    public function ensurePermissionsExist(array $names): void
    {
        foreach ($names as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }

    /**
     * Sync all hook-registered permissions into the database.
     */
    public function syncRegisteredPermissions(): int
    {
        $names = $this->allPermissionNames();
        $this->ensurePermissionsExist($names);
        return count($names);
    }

    // ─── User role assignment ────────────────────────────────

    /**
     * Assign a role to a user within an instance scope.
     */
    public function assignRoleToUser($user, string $roleName, int $instanceId): void
    {
        $registrar = app(PermissionRegistrar::class);

        // Find role in global scope (roles are created at team_id=0)
        $registrar->setPermissionsTeamId(0);
        $role = Role::findByName($roleName, 'web');

        // Assign in instance scope
        $registrar->setPermissionsTeamId($instanceId);
        $user->assignRole($role);

        $registrar->setPermissionsTeamId(0);
        $user->unsetRelation('roles')->unsetRelation('permissions');
    }

    /**
     * Remove all roles from user in an instance, then assign new one.
     */
    public function syncUserRole($user, string $roleName, int $instanceId): void
    {
        $registrar = app(PermissionRegistrar::class);

        // Remove existing roles for this user in this instance
        DB::connection('system')
            ->table('model_has_roles')
            ->where('model_id', $user->id)
            ->where('model_type', get_class($user))
            ->where(config('permission.column_names.team_foreign_key', 'instance_id'), $instanceId)
            ->delete();

        // Find role in global scope
        $registrar->setPermissionsTeamId(0);
        $role = Role::findByName($roleName, 'web');

        // Assign in instance scope
        $registrar->setPermissionsTeamId($instanceId);
        $user->unsetRelation('roles')->unsetRelation('permissions');
        $user->assignRole($role);

        $registrar->setPermissionsTeamId(0);
        $user->unsetRelation('roles')->unsetRelation('permissions');
    }

    /**
     * Get the role name for a user in a specific instance.
     */
    public function userRoleInInstance($user, int $instanceId): ?string
    {
        $teamFk = config('permission.column_names.team_foreign_key', 'instance_id');

        return DB::connection('system')
            ->table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', get_class($user))
            ->where('model_has_roles.model_id', $user->id)
            ->where("model_has_roles.{$teamFk}", $instanceId)
            ->value('roles.name');
    }

    /**
     * Count users assigned to a role.
     */
    public function usersWithRole(Role $role): int
    {
        return DB::connection('system')
            ->table('model_has_roles')
            ->where('role_id', $role->id)
            ->count();
    }
}
