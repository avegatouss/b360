<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Support\TeamContext;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeder RBAC du module Core.
 *
 * NOTE : Ce seeder est un COMPLÉMENT au RolesPermissionsSeeder principal
 * (database/seeders/). Il ne crée que les permissions/rôles spécifiques
 * au module Core qui ne sont pas déjà gérés par le seeder principal.
 *
 * Le seeder principal (RolesPermissionsSeeder) est exécuté par l'Installer
 * et gère la création initiale de TOUS les rôles et permissions de base.
 * Ce seeder-ci peut être exécuté indépendamment pour ajouter des
 * permissions module-spécifiques.
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
            // Permissions additionnelles Core
            $permissions = [
                'instances.view', 'instances.manage',
                'users.view', 'users.manage',
                'modules.view', 'modules.manage',
                'settings.view', 'settings.manage',
            ];

            foreach ($permissions as $perm) {
                Permission::findOrCreate($perm);
            }

            // S'assurer que les rôles existent (idempotent)
            Role::findOrCreate('super-admin');
            $instanceAdmin = Role::findOrCreate('instance-admin');
            $user = Role::findOrCreate('user');

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
        } finally {
            // Restaurer le contexte
            $registrar->setPermissionsTeamId(TeamContext::GLOBAL_TEAM_ID);
        }
    }
}
