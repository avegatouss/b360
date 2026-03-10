<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeder des rôles et permissions de base de B360.
 *
 * Stratégie team_id pour Spatie Permission :
 * ─────────────────────────────────────────────
 * Spatie stocke les rôles utilisateurs dans model_has_roles avec :
 *   instance_id (team_foreign_key) NOT NULL (partie de la PRIMARY KEY MySQL)
 *
 * On ne peut donc pas utiliser NULL pour les rôles "globaux".
 * Convention B360 :
 *   instance_id = 0  → rôle global / cross-instance  (ex: super-admin)
 *   instance_id = N  → rôle scoped à l'instance N
 *
 * Gate::before vérifiera toujours avec team_id=0 pour super-admin.
 */
class RolesPermissionsSeeder extends Seeder
{
    // Convention : 0 = contexte global (pas d'instance)
    public const GLOBAL_TEAM_ID = 0;

    public function run(): void
    {
        $registrar = app(PermissionRegistrar::class);

        // Réinitialiser le cache
        $registrar->forgetCachedPermissions();

        // Contexte global pour tous les rôles templates
        $registrar->setPermissionsTeamId(self::GLOBAL_TEAM_ID);

        /*
        |----------------------------------------------------------------------
        | Permissions (sans team context — partagées)
        |----------------------------------------------------------------------
        */
        $permissions = [
            'dashboard.view',
            'users.view',
            'users.manage',
            'instances.view',
            'instances.manage',
            'modules.view',
            'modules.manage',
            'settings.view',
            'settings.manage',
            'billing.view',
            'billing.manage',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        /*
        |----------------------------------------------------------------------
        | Rôle global : super-admin (instance_id = 0)
        | Gate::before bypass → pas besoin de permissions explicites
        |----------------------------------------------------------------------
        */
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

        /*
        |----------------------------------------------------------------------
        | Rôles templates (instance_id = 0)
        | Seront recréés avec instance_id = N quand une instance est configurée.
        |----------------------------------------------------------------------
        */
        $instanceAdmin = Role::firstOrCreate(['name' => 'instance-admin', 'guard_name' => 'web']);
        $instanceAdmin->syncPermissions([
            'dashboard.view',
            'users.view',
            'users.manage',
            'instances.view',
            'billing.view',
            'billing.manage',
            'settings.view',
            'settings.manage',
        ]);

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'dashboard.view',
            'users.view',
        ]);

        $agent = Role::firstOrCreate(['name' => 'agent', 'guard_name' => 'web']);
        $agent->syncPermissions(['dashboard.view']);

        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $user->syncPermissions(['dashboard.view']);

        // Restaurer au contexte global (convention B360 : 0 = global, null interdit en PK)
        $registrar->setPermissionsTeamId(self::GLOBAL_TEAM_ID);
        $registrar->forgetCachedPermissions();
    }
}
