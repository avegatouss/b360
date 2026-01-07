<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

final class CoreRbacSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure fresh permission cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Permissions (global definitions; team_id null)
        $permissions = [
            'instances.view', 'instances.manage',
            'users.view', 'users.manage',
            'modules.view', 'modules.manage',
            'settings.view', 'settings.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm);
        }

        // Roles (defined globally; assigned per instance through team_id pivot)
        $superAdmin = Role::findOrCreate('super-admin');
        $instanceAdmin = Role::findOrCreate('instance-admin');
        $user = Role::findOrCreate('user');

        // Minimal default permission mapping
        $instanceAdmin->syncPermissions([
            'instances.view',
            'users.view', 'users.manage',
            'modules.view',
            'settings.view', 'settings.manage',
        ]);

        $user->syncPermissions([
            'instances.view',
            'users.view',
            'modules.view',
            'settings.view',
        ]);

        // super-admin handled via Gate::before, no need to assign all permissions.
    }
}
