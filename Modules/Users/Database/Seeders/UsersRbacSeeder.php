<?php

namespace Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Support\TeamContext;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeder RBAC du module Users.
 *
 * COMPLÉMENT au RolesPermissionsSeeder principal.
 * Ajoute les permissions et rôles spécifiques au module Users
 * qui ne sont pas déjà gérés par le seeder principal.
 */
final class UsersRbacSeeder extends Seeder
{
    public function run(): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        // Forcer le contexte global (instance_id = 0)
        $registrar->setPermissionsTeamId(TeamContext::GLOBAL_TEAM_ID);

        try {
            $perms = [
                'users.view', 'users.manage',
                'dashboard.view',
                'settings.view', 'settings.manage',
            ];

            foreach ($perms as $p) {
                Permission::findOrCreate($p);
            }

            // S'assurer que les rôles existent (idempotent)
            $instanceAdmin = Role::findOrCreate('instance-admin');
            $manager = Role::findOrCreate('manager');
            $agent = Role::findOrCreate('agent');

            $instanceAdmin->syncPermissions([
                'users.view', 'users.manage', 'dashboard.view', 'settings.view', 'settings.manage',
            ]);

            $manager->syncPermissions([
                'users.view', 'dashboard.view', 'settings.view',
            ]);

            $agent->syncPermissions([
                'dashboard.view',
            ]);
        } finally {
            $registrar->setPermissionsTeamId(TeamContext::GLOBAL_TEAM_ID);
        }
    }
}
