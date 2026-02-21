<?php

namespace Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

final class UsersRbacSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $perms = [
            'users.view', 'users.manage',
            'dashboard.view',
            'settings.view', 'settings.manage',
        ];

        foreach ($perms as $p) {
            Permission::findOrCreate($p);
        }

        // Instance-level roles (team-scoped when assigned)
        Role::findOrCreate('instance-admin');
        Role::findOrCreate('manager');
        Role::findOrCreate('agent');

        // Permission mapping (minimal)
        Role::findByName('instance-admin')->syncPermissions([
            'users.view', 'users.manage', 'dashboard.view', 'settings.view', 'settings.manage'
        ]);

        Role::findByName('manager')->syncPermissions([
            'users.view', 'dashboard.view', 'settings.view'
        ]);

        Role::findByName('agent')->syncPermissions([
            'dashboard.view'
        ]);
    }
}
