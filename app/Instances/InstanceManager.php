<?php

namespace App\Instances;

use App\Installer\InstallLock;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Applique le contexte DB de l'Instance courante.
 *
 * Règles MVP :
 * - DB "system" : centrale (instances + users)
 * - Shared : pas de switching DB (isolation via instance_id sur tables métiers plus tard)
 * - Database-per-instance : connexion "instance" dynamique
 */

class InstanceManager
{
    /**
     * Applique la configuration de base de données
     * pour l’instance courante.
     */
    public function apply(Instance $instance): void
    {

         if (!config('app.installed', false) || !InstallLock::isInstalled()) {
            throw new RuntimeException('InstanceManager appelé avant installation.');
        }

        $mode = config('app.instance_mode', 'single');
        $strategy = config('app.instance_db_strategy', 'shared');

        // Toujours repartir sur la DB centrale pour éviter les fuites
        DB::setDefaultConnection('system');
        DB::purge('instance');
        app()->forgetInstance('currentInstanceConnection');
        // Single => pas de séparation
        // Single DB : tout est system
        if ($mode === 'single') {
            return;
        }

        if (!in_array($mode, ['single', 'multi'], true)) {
             throw new RuntimeException('Mode Instance invalide.');
        }


        if ($strategy === 'shared') {
            // Shared : isolation via GlobalScopes (à implémenter sur tables métiers).
            return;
        }

        if (!in_array($strategy, ['shared', 'database-per-instance'], true)) {
            throw new RuntimeException('Stratégie DB Instance invalide.');
        }

        if (!$instance->database) {
            throw new RuntimeException('Database manquante pour l’instance.');
        }

        // On clone la connexion system, puis on change uniquement la DB cible
        $driver = $instance->db_driver ?: config('database.connections.system.driver', 'mysql');

        // Connexion instance (sans env() direct)
        $system = Config::get('database.connections.system');

        Config::set('database.connections.instance', array_replace($system, [
            'driver'   => $driver,
            'database' => $instance->database,
        ]));

        DB::purge('instance');
        DB::reconnect('instance');
        Log::debug('Instance DB context applied', [
            'instance_id' => $instance->id,
            'strategy' => $strategy,
        ]);
        // Par convention, la DB "métier" doit utiliser connection('instance') explicitement
        // OU on peut définir un "currentInstanceConnection" dans le container.
        app()->instance('currentInstanceConnection', 'instance');
    }
}
