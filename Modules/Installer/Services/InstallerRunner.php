<?php

namespace Modules\Installer\Services;

use App\Installer\InstallLock;
use App\Instances\DatabaseCreator;
use Database\Seeders\InstanceSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * InstallerRunner — Orchestre l'installation complète de B360.
 *
 * Garanties :
 * - Anti-concurrence (InstallLock)
 * - Rollback minimal sur échec (.env restauré, lock libéré)
 * - Progression SSE temps réel (callbacks)
 * - Messages d'erreur lisibles, sans fuite de secrets
 */
class InstallerRunner
{
    public function run(array $data, ?callable $progress = null): void
    {
        if (config('app.installed', false) === true || InstallLock::isInstalled()) {
            throw new RuntimeException('Application déjà installée.');
        }

        $runId = $data['run_id'] ?? bin2hex(random_bytes(16));

        $emit = function (int $percent, string $message) use ($progress): void {
            if ($progress) {
                $progress($percent, $message);
            }
        };

        $safeContext = [
            'run_id'               => $runId,
            'db_connection'        => $data['db_connection'] ?? null,
            'db_host'              => $data['db_host'] ?? null,
            'db_database'          => $data['db_database'] ?? null,
            'instance_mode'        => $data['instance_mode'] ?? null,
            'instance_db_strategy' => $data['instance_db_strategy'] ?? null,
        ];

        InstallLock::acquire($runId);
        Log::info('installer.start', $safeContext);

        try {
            $mode     = $data['instance_mode'] ?? 'single';
            $strategy = $data['instance_db_strategy'] ?? 'shared';

            if (!in_array($mode, ['single', 'multi'], true)) {
                throw new RuntimeException('Mode instance invalide. Valeurs acceptées : single, multi.');
            }
            if (!in_array($strategy, ['shared', 'database-per-instance'], true)) {
                throw new RuntimeException('Stratégie DB invalide. Valeurs acceptées : shared, database-per-instance.');
            }

            // ── Étape 1 : .env ───────────────────────────────────────────────
            $emit(10, 'Écriture du fichier de configuration (.env)…');
            $this->step('Écriture .env', function () use ($data) {
                app(EnvWriter::class)->write($data);
            });

            // ── Étape 2 : cache ───────────────────────────────────────────────
            $emit(18, 'Nettoyage des caches Laravel…');
            $this->step('Nettoyage config', function () {
                if (Artisan::call('config:clear') !== 0) {
                    throw new RuntimeException('Échec du nettoyage de la configuration.');
                }
            });
            $this->step('Nettoyage cache', function () {
                if (Artisan::call('cache:clear') !== 0) {
                    throw new RuntimeException('Échec du nettoyage du cache.');
                }
            });

            // Synchroniser la config en mémoire (config:clear efface le fichier
            // mais pas les valeurs déjà chargées dans la request courante).
            // Sans ça, DatabaseCreator et les seeders liront les anciennes valeurs.
            Config::set('app.name',                 $data['app_name'] ?? 'B360');
            Config::set('app.url',                  $data['app_url']  ?? 'http://localhost');
            Config::set('app.instance_mode',        $mode);
            Config::set('app.instance_db_strategy', $strategy);

            // ── Étape 3 : connexion DB ────────────────────────────────────────
            $emit(25, 'Connexion à la base de données…');
            $this->step('Connexion base de données', function () use ($data) {
                $this->configureDatabaseConnection($data);
                try {
                    DB::connection('system')->getPdo();
                } catch (Throwable $e) {
                    throw new RuntimeException(
                        'Impossible de se connecter à la base de données. ' .
                        'Vérifiez l\'hôte, le port, le nom de la base et les identifiants.'
                    );
                }
            });

            // ── Étape 4 : migrations ──────────────────────────────────────────
            // On utilise migrate:fresh pour garantir un schéma propre.
            // L'installeur est un outil first-run : la DB doit être vierge.
            // Si une tentative précédente a échoué et laissé des tables avec
            // un schéma obsolète, migrate (sans fresh) ne les mettrait pas à jour.
            $emit(38, 'Exécution des migrations de base de données…');
            $this->step('Migrations', function () {
                $exitCode = Artisan::call('migrate:fresh', ['--force' => true]);
                if ($exitCode !== 0) {
                    throw new RuntimeException(
                        'Les migrations ont échoué (code ' . $exitCode . '). ' .
                        'Vérifiez que l\'utilisateur DB possède les droits CREATE TABLE.'
                    );
                }
            });

            // ── Étape 5 : DB instance ROOT (mode multi uniquement) ────────────
            if ($mode === 'multi' && $strategy === 'database-per-instance') {
                $emit(50, 'Création de la base de données de l\'instance ROOT…');
                $this->step('Création DB ROOT', function () use ($data) {
                    $rootSlug     = \Illuminate\Support\Str::slug($data['instance_root_slug'] ?? ($data['app_name'] ?? 'b360'));
                    $databaseName = ($data['db_prefix'] ?? '') . $rootSlug . ($data['db_suffix'] ?? '');
                    $databaseName = substr($databaseName, 0, 64);

                    app(DatabaseCreator::class)->create($databaseName);
                });
            }

            // ── Étape 6 : instance ROOT ───────────────────────────────────────
            $emit(60, 'Création de l\'instance ROOT…');
            $this->step('Instance ROOT', function () {
                $exitCode = Artisan::call('db:seed', [
                    '--class' => InstanceSeeder::class,
                    '--force' => true,
                ]);
                if ($exitCode !== 0) {
                    throw new RuntimeException(
                        'Le seeder InstanceSeeder a échoué (code ' . $exitCode . ').'
                    );
                }
            });

            // ── Étape 7 : rôles & permissions ────────────────────────────────
            $emit(72, 'Initialisation des rôles et permissions…');
            $this->step('Rôles & permissions', function () {
                $exitCode = Artisan::call('db:seed', [
                    '--class' => RolesPermissionsSeeder::class,
                    '--force' => true,
                ]);
                if ($exitCode !== 0) {
                    throw new RuntimeException(
                        'Le seeder RolesPermissionsSeeder a échoué (code ' . $exitCode . ').'
                    );
                }
            });

            // ── Étape 8 : super admin ─────────────────────────────────────────
            Config::set('installer.admin', [
                'username'   => $data['admin_username'] ?? null,
                'email'      => $data['admin_email'] ?? null,
                'password'   => $data['admin_password'] ?? null,
                'first_name' => $data['admin_firstname'] ?? null,
                'last_name'  => $data['admin_lastname'] ?? null,
            ]);

            $emit(84, 'Création du compte Super Administrateur…');
            $this->step('Super Administrateur', function () {
                $exitCode = Artisan::call('db:seed', [
                    '--class' => SuperAdminSeeder::class,
                    '--force' => true,
                ]);
                if ($exitCode !== 0) {
                    throw new RuntimeException(
                        'Le seeder SuperAdminSeeder a échoué (code ' . $exitCode . ').'
                    );
                }
            });

            // ── Étape 9 : finalisation ────────────────────────────────────────
            $emit(94, 'Finalisation de l\'installation…');
            InstallLock::markInstalled($runId);
            app(EnvWriter::class)->markInstalled();

            $emit(98, 'Nettoyage des caches…');
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            Log::info('installer.done', $safeContext);
            $emit(100, 'Installation terminée avec succès. Redirection…');

        } catch (Throwable $e) {
            Log::error('installer.failed', $safeContext + [
                'error'       => $this->sanitizeError($e->getMessage()),
                'error_class' => get_class($e),
            ]);

            // Rollback minimal
            try {
                app(EnvWriter::class)->restoreBackup();
            } catch (Throwable) {}

            InstallLock::releaseInstalling();

            throw $e;
        }
    }

    /**
     * Exécute une étape en wrappant les exceptions avec un contexte lisible.
     */
    private function step(string $stepName, callable $callback): void
    {
        try {
            $callback();
        } catch (RuntimeException $e) {
            // Message déjà lisible → on laisse passer
            throw $e;
        } catch (Throwable $e) {
            // Exception technique → on encapsule avec contexte
            throw new RuntimeException(
                "Échec à l'étape « {$stepName} » : " . $this->sanitizeError($e->getMessage()),
                0,
                $e
            );
        }
    }

    /**
     * Configure la connexion "system" en runtime depuis les données du wizard.
     */
    protected function configureDatabaseConnection(array $data): void
    {
        $connection = $data['db_connection'] ?? 'mysql';

        Config::set('database.default', 'system');
        Config::set('database.connections.system', [
            'driver'    => $connection,
            'host'      => $data['db_host'] ?? '127.0.0.1',
            'port'      => $data['db_port'] ?? 3306,
            'database'  => $data['db_database'] ?? null,
            'username'  => $data['db_username'] ?? null,
            'password'  => $data['db_password'] ?? '',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
        ]);

        DB::purge('system');
        DB::reconnect('system');
    }

    /**
     * Masque les secrets sensibles avant log / affichage.
     */
    private function sanitizeError(string $msg): string
    {
        // Masquer les mots de passe dans tous les formats courants
        $msg = preg_replace('/(password|passwd|pwd)[=:]\s*[^\s,;\]\)"\']+/i', '$1=***', $msg);
        $msg = preg_replace('/(DB_PASSWORD=).*/i', '$1***', $msg);
        // Masquer les DSN complets
        $msg = preg_replace('/(mysql|pgsql|sqlsrv):\/\/[^@]+@/i', '$1://***:***@', $msg);
        $msg = preg_replace('/(mysql:host=)[^;]+/i', '$1***', $msg);
        $msg = preg_replace('/(pgsql:host=)[^;]+/i', '$1***', $msg);
        // Patterns Spatie Permission souvent verbeux
        $msg = preg_replace('/SQLSTATE\[.*?\]/s', 'Erreur base de données', $msg);

        return mb_substr($msg, 0, 350);
    }
}
