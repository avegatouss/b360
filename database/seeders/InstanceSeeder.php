<?php

namespace Database\Seeders;

use App\Instances\Instance;
use Illuminate\Database\Seeder;

/**
 * Seed de l'Instance ROOT.
 *
 * MVP :
 * - users restent system (global)
 * - root instance sert de "fallback" et de base de configuration
 */
class InstanceSeeder extends Seeder
{
    public function run(): void
    {
        $mode = config('app.instance_mode', 'single');
        $strategy = config('app.instance_db_strategy', 'shared');

        $rootSlug = 'root';
        $rootDomain = strtolower(parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost');

        // En database-per-instance, on renseignera "database" plus tard (ou via l’installer)
        // Pour le MVP, on laisse null si on ne sait pas, mais l’idempotence est garantie.
        $database = null;

        if ($mode === 'multi' && $strategy === 'database-per-instance') {
            // Option : si tu veux pointer sur une DB instance créée lors du runner,
            // tu peux la recalculer ici avec prefix/suffix + app_name,
            // mais cela dépend des données wizard. MVP : on garde null.
            $database = null;
        }

        Instance::query()->updateOrCreate(
            ['slug' => $rootSlug],
            [
                'name' => 'B360 Root',
                'domain' => $rootDomain,
                'subdomain' => null,
                'database' => $database,
                'db_driver' => config('database.connections.system.driver'),
                'is_active' => true,
                'installed_at' => now(),
                'meta' => [
                    'is_root' => true,
                ],
            ]
        );
    }
}
