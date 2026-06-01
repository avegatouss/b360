<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Support\TeamContext;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeder RBAC du module Core.
 *
 * COMPLÉMENT au RolesPermissionsSeeder principal (database/seeders/).
 * Ne crée que les permissions/rôles spécifiques au module Core
 * qui ne sont pas déjà gérés par le seeder principal.
 *
 * Peut être exécuté indépendamment et de manière idempotente.
 */
final class CoreRbacSeeder extends Seeder
{
    public function run(): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        // Forcer le contexte global (instance_id = 0) pour la création des rôles
        $registrar->setPermissionsTeamId(TeamContext::GLOBAL_TEAM_ID);

        try {
            // Permissions Core (subset des permissions principales)
            $permissions = [
                'dashboard.view',
                'instances.view', 'instances.manage',
                'users.view', 'users.manage',
                'modules.view', 'modules.manage',
                'settings.view', 'settings.manage',
                'billing.view', 'billing.manage',
                'admin.settings', 'admin.users', 'admin.roles',
                'admin.modules', 'admin.maintenance',
            ];

            // Use Eloquent firstOrCreate (DB-authoritative) instead of Spatie's
            // findOrCreate which can return cached entries pointing to rolled-back rows
            // when running in parallel test mode with array cache driver.
            foreach ($permissions as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            }

            // Force fresh cache after bulk permission creation, before role grants.
            $registrar->forgetCachedPermissions();

            // S'assurer que les rôles existent (idempotent)
            Role::findOrCreate('super-admin');

            $instanceAdmin = Role::findOrCreate('instance-admin');
            $instanceAdmin->givePermissionTo([
                'dashboard.view',
                'instances.view', 'instances.manage',
                'users.view', 'users.manage',
                'modules.view', 'modules.manage',
                'settings.view', 'settings.manage',
                'billing.view', 'billing.manage',
                'admin.settings', 'admin.users', 'admin.roles',
                'admin.modules', 'admin.maintenance',
            ]);

            $manager = Role::findOrCreate('manager');
            $manager->givePermissionTo([
                'dashboard.view',
                'instances.view',
                'users.view',
                'modules.view',
                'settings.view',
            ]);

            $agent = Role::findOrCreate('agent');
            $agent->givePermissionTo([
                'dashboard.view',
            ]);

            $user = Role::findOrCreate('user');
            $user->givePermissionTo([
                'dashboard.view',
                'instances.view',
            ]);
        } finally {
            // Restaurer le contexte
            $registrar->setPermissionsTeamId(TeamContext::GLOBAL_TEAM_ID);
        }
    }
}
