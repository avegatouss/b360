<?php

namespace App\Instances;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Class InstanceManager
 *
 * Applique le mode Instance défini à l’installation.
 */
class InstanceManager
{
    /**
     * Applique la configuration de base de données
     * pour l’instance courante.
     */
    public function apply(Instance $instance): void
    {
        $mode = config('app.instance_mode');

        if ($mode === 'single') {
            // Single DB : rien à faire
            return;
        }

        if ($mode !== 'multi') {
            throw new RuntimeException('Mode Instance invalide.');
        }

        if (!$instance->database) {
            throw new RuntimeException('Database manquante pour l’instance.');
        }

        /*
        |--------------------------------------------------------------------------
        | Connexion dynamique
        |--------------------------------------------------------------------------
        */
        Config::set('database.connections.instance', [
            'driver'   => 'mysql',
            'host'     => env('DB_HOST'),
            'port'     => env('DB_PORT'),
            'database' => $instance->database,
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset'  => 'utf8mb4',
            'collation'=> 'utf8mb4_unicode_ci',
            'prefix'   => '',
            'strict'   => true,
        ]);

        DB::setDefaultConnection('instance');
    }
}
