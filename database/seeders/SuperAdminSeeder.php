<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\PermissionRegistrar;

/**
 * Crée le Super Administrateur initial de B360.
 *
 * Dépendances (à exécuter avant) :
 *   1. InstanceSeeder      → instance ROOT doit exister
 *   2. RolesPermissionsSeeder → rôle 'super-admin' (instance_id=0) doit exister
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = config('installer.admin');
        $now   = now();

        if (!$admin || empty($admin['username']) || empty($admin['email']) || empty($admin['password'])) {
            throw new RuntimeException('Données Super Admin manquantes dans config(installer.admin).');
        }

        $fullName = trim(($admin['first_name'] ?? '') . ' ' . ($admin['last_name'] ?? ''));

        /*
        |----------------------------------------------------------------------
        | Upsert utilisateur (idempotent si relance)
        |----------------------------------------------------------------------
        */
        $exists = DB::table('users')->where('email', $admin['email'])->exists();

        $data = [
            'username'   => $admin['username'],
            'password'   => Hash::make($admin['password']),
            'first_name' => $admin['first_name'] ?? null,
            'last_name'  => $admin['last_name']  ?? null,
            'full_name'  => $fullName ?: null,
            'is_active'  => true,
            'is_blocked' => false,
            'updated_at' => $now,
            'created_at' => $exists ? DB::raw('created_at') : $now,
        ];

        if (!$exists) {
            $data['uuid'] = Str::uuid()->toString();
        }

        DB::table('users')->updateOrInsert(
            ['email' => $admin['email']],
            $data
        );

        $user = User::where('email', $admin['email'])->firstOrFail();

        /*
        |----------------------------------------------------------------------
        | Rôle super-admin — contexte GLOBAL (instance_id = 0)
        |
        | Convention B360 : instance_id = 0 = rôle cross-instance.
        | La PK MySQL de model_has_roles est NOT NULL → null interdit.
        | Gate::before vérifiera toujours avec team_id = 0.
        |----------------------------------------------------------------------
        */
        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId(0); // contexte global

        if (!$user->hasRole('super-admin')) {
            $user->assignRole('super-admin');
        }

        // Restaurer au contexte global (convention B360 : 0 = global, null interdit en PK)
        $registrar->setPermissionsTeamId(0);
        $registrar->forgetCachedPermissions();

        /*
        |----------------------------------------------------------------------
        | Membership ROOT : membre actif de l'instance ROOT
        |----------------------------------------------------------------------
        */
        $rootInstance = DB::connection('system')
            ->table('instances')
            ->where('slug', 'root')
            ->first();

        if ($rootInstance) {
            DB::connection('system')->table('instance_user')->updateOrInsert(
                ['instance_id' => $rootInstance->id, 'user_id' => $user->id],
                ['status' => 'active', 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
