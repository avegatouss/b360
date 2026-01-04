<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = config('installer.admin');

        if (!$admin || empty($admin['username']) || empty($admin['email']) || empty($admin['password'])) {
            throw new RuntimeException('Données Super Admin manquantes dans config(installer.admin).');
        }

        $fullName = trim(($admin['first_name'] ?? '') . ' ' . ($admin['last_name'] ?? ''));

        /*
        |----------------------------------------------------------------------
        | Upsert (évite doublons si relance)
        |----------------------------------------------------------------------
        */
        DB::table('users')->updateOrInsert(
            ['email' => $admin['email']],
            [
                'username'   => $admin['username'],
                'password'   => Hash::make($admin['password']),

                'first_name' => $admin['first_name'] ?? null,
                'last_name'  => $admin['last_name'] ?? null,
                'full_name'  => $fullName ?: null,

                'is_active'  => true,
                'is_blocked' => false,

                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
