<?php

namespace Database\Seeders;

use App\Instances\Instance;
use App\Installer\InstallLock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Seed de l'Instance ROOT.
 *
 * Règles absolues :
 * - Exécuté UNIQUEMENT pendant l’Installer
 * - Écrit uniquement dans la DB "system"
 * - Idempotent
 */
class InstanceSeeder extends Seeder
{
    public function run(): void
    {
        // Barrière dure : jamais après installation
        if (config('app.installed', false) === true || InstallLock::isInstalled()) {
            throw new RuntimeException('InstanceSeeder exécuté après installation.');
        }

        DB::connection('system')->transaction(function () {
            $rootSlug = 'root';
            $rootDomain = strtolower(
                parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost'
            );

            $instance = Instance::query()->firstOrNew([
                'slug' => $rootSlug,
            ]);

            $instance->name = 'B360 Root';
            $instance->domain = $rootDomain;
            $instance->database = null; // ROOT n’a jamais de DB dédiée au MVP
            $instance->db_driver = config('database.connections.system.driver');
            $instance->is_active = true;

            $instance->meta = array_merge(
                (array) $instance->meta,
                ['is_root' => true]
            );

            if (!$instance->installed_at) {
                $instance->installed_at = now();
            }

            $instance->save();
        });
    }
}
