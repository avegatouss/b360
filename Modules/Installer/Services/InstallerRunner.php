<?php

namespace Modules\Installer\Services;

use App\Installer\InstallLock;
use App\Instances\DatabaseCreator;
use Database\Seeders\InstanceSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * InstallerRunner
 *
 * Objectifs :
 * - Installation robuste (anti-concurrence, rollback minimal)
 * - Idempotence raisonnable (seeders upsert)
 * - Aucun secret en logs
 */
class InstallerRunner
{
    public function run(array $data, ?callable $progress = null): void
    {
        if (config('app.installed', false) === true || InstallLock::isInstalled()) {
            throw new RuntimeException('Application déjà installée.');
        }

        // Correlation / run id : permet tracer l’installation en logs et côté UI
        $runId = $data['run_id'] ?? bin2hex(random_bytes(16));

        $emit = function (int $percent, string $message) use ($progress): void {
            if ($progress) {
                $progress($percent, $message);
            }
        };

        // Context SAFE (pas de password)
        $safeContext = [
            'run_id' => $runId,
            'db_connection' => $data['db_connection'] ?? null,
            'db_host' => $data['db_host'] ?? null,
            'db_port' => $data['db_port'] ?? null,
            'db_database' => $data['db_database'] ?? null,
            'instance_mode' => $data['instance_mode'] ?? null,
            'instance_db_strategy' => $data['instance_db_strategy'] ?? null,
        ];

        // Lock anti-concurrence
        InstallLock::acquire($runId);
        Log::info('installer.start', $safeContext);

        try {
            $emit(10, 'Écriture du fichier .env...');
            app(EnvWriter::class)->write($data);

            $emit(18, 'Nettoyage du cache configuration...');
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            $emit(25, 'Initialisation de la connexion base de données...');
            $this->configureDatabaseConnection($data);

            // Test DB (côté Laravel) après injection config runtime
            try {
                DB::connection('system')->getPdo();
            } catch (Throwable) {
                throw new RuntimeException('Connexion à la base de données impossible.');
            }

            $emit(40, 'Exécution des migrations...');
            Artisan::call('migrate', ['--force' => true]);

            // Création DB instance ROOT uniquement si multi + database-per-instance
            $mode = $data['instance_mode'] ?? 'single';
            $strategy = $data['instance_db_strategy'] ?? 'shared';

            if ($mode === 'multi' && $strategy === 'database-per-instance') {
                $emit(55, 'Création de la base de données de l’instance ROOT...');

                $databaseName = ($data['db_prefix'] ?? '')
                    . \Illuminate\Support\Str::slug($data['app_name'] ?? 'b360')
                    . ($data['db_suffix'] ?? '');

                app(DatabaseCreator::class)->create($databaseName);
            }

            $emit(70, 'Génération des données Instance ROOT...');
            Artisan::call('db:seed', [
                '--class' => InstanceSeeder::class,
                '--force' => true,
            ]);

            // Injection admin dans la config runtime (seeders = contexte CLI / SSE)
            Config::set('installer.admin', [
                'username'   => $data['admin_username'] ?? null,
                'email'      => $data['admin_email'] ?? null,
                'password'   => $data['admin_password'] ?? null,
                'first_name' => $data['admin_firstname'] ?? null,
                'last_name'  => $data['admin_lastname'] ?? null,
            ]);

            $emit(85, 'Création du compte Super Administrateur...');
            Artisan::call('db:seed', [
                '--class' => SuperAdminSeeder::class,
                '--force' => true,
            ]);

            $emit(95, 'Finalisation de l’installation...');
            app(EnvWriter::class)->markInstalled();

            // Lock définitif
            InstallLock::markInstalled($runId);

            $emit(98, 'Nettoyage final des caches...');
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            Log::info('installer.done', $safeContext);
            $emit(100, 'Installation terminée.');
        } catch (Throwable $e) {
            Log::error('installer.failed', $safeContext + [
                'error' => $this->sanitizeError($e->getMessage()),
            ]);

            // Rollback minimal : restaurer .env + libérer lock installing
            try {
                app(EnvWriter::class)->restoreBackup();
            } catch (Throwable) {
                // Ne jamais masquer l’erreur principale
            }

            InstallLock::releaseInstalling();

            throw $e;
        }
    }

    /**
     * Configure la connexion "system" (DB centrale) en runtime.
     *
     * Pourquoi ?
     * - Pendant l'installation, on veut utiliser les paramètres saisis au wizard
     * - On évite une config précédente/cachée
     */
    protected function configureDatabaseConnection(array $data): void
    {
        $connection = $data['db_connection'] ?? 'mysql';

        // On force database.default = system (logique)
        Config::set('database.default', 'system');

        // On initialise system avec la config de base du driver choisi
        Config::set('database.connections.system', [
            'driver' => $connection,
            'host' => $data['db_host'] ?? '127.0.0.1',
            'port' => $data['db_port'] ?? 3306,
            'database' => $data['db_database'] ?? null,
            'username' => $data['db_username'] ?? null,
            'password' => $data['db_password'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
        ]);

        DB::purge('system');
        DB::reconnect('system');
    }

    /**
     * Sanitize pour éviter toute fuite de secrets dans les logs.
     */
    private function sanitizeError(string $msg): string
    {
        $msg = preg_replace('/(password=)[^;]+/i', '$1***', $msg);
        $msg = preg_replace('/(DB_PASSWORD=).*/i', '$1***', $msg);
        return $msg;
    }
}
