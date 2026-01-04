<?php

namespace Modules\Installer\Services;

use App\Instances\DatabaseCreator;
use Database\Seeders\InstanceSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InstallerRunner
{
    public function run(array $data, ?callable $progress = null): void
    {
        /*
    |----------------------------------------------------------------------
    | Sécurité : déjà installé
    |----------------------------------------------------------------------
    */
        if (config('app.installed', false) === true) {
            throw new RuntimeException('Application déjà installée.');
        }

        $emit = function (int $percent, string $message) use ($progress): void {
            if ($progress) {
                $progress($percent, $message);
            }
        };

        /*
    |----------------------------------------------------------------------
    | 1) Écriture du .env (APP_INSTALLED=false)
    |----------------------------------------------------------------------
    */
        $emit(10, 'Écriture du fichier .env...');
        app(EnvWriter::class)->write($data);

        /*
    |----------------------------------------------------------------------
    | 2) Nettoyage caches et rechargement configuration
    |----------------------------------------------------------------------
    */
        $emit(18, 'Nettoyage du cache configuration...');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        /*
    |----------------------------------------------------------------------
    | 3) Appliquer la configuration DB d'installation à la connexion Laravel
    |----------------------------------------------------------------------
    |
    | Important : pendant l'installation, on doit forcer la connexion Laravel
    | à utiliser les paramètres saisis dans l'étape 2, sinon DB::connection()
    | peut utiliser une configuration précédente.
    |
    */
        $emit(25, 'Initialisation de la connexion base de données...');
        $this->configureDatabaseConnection($data);

        /*
    |----------------------------------------------------------------------
    | 4) Test connexion DB
    |----------------------------------------------------------------------
    */
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            throw new RuntimeException('Connexion à la base de données impossible.');
        }

        /*
    |----------------------------------------------------------------------
    | 5) Migrations centrales
    |----------------------------------------------------------------------
    */
        $emit(40, 'Exécution des migrations...');
        Artisan::call('migrate', ['--force' => true]);

        /*
    |----------------------------------------------------------------------
    | 6) Création base Instance (Multi DB uniquement)
    |----------------------------------------------------------------------
    |
    | En mode multi, on crée la DB de l'instance ROOT (ou instance par défaut).
    | Les champs db_prefix/db_suffix ont été validés et stockés à l'étape 3.
    |
    */
        if (($data['instance_mode'] ?? 'single') === 'multi') {
            $emit(55, 'Création de la base de données de l’instance...');

            $databaseName = ($data['db_prefix'] ?? '')
                . \Illuminate\Support\Str::slug($data['app_name'] ?? 'b360')
                . ($data['db_suffix'] ?? '');

            app(DatabaseCreator::class)->create($databaseName);
        }

        /*
    |----------------------------------------------------------------------
    | 7) Seed Instance ROOT
    |----------------------------------------------------------------------
    */
        $emit(70, 'Génération des données Instance...');
        Artisan::call('db:seed', [
            '--class' => InstanceSeeder::class,
            '--force' => true,
        ]);

        /*
    |----------------------------------------------------------------------
    | 8) Seed Super Admin
    |----------------------------------------------------------------------
    |
    */
        /*
    |----------------------------------------------------------------------
    | Injection des données Admin pour le seeder
    |----------------------------------------------------------------------
    |
    | Un seeder ne doit pas dépendre de session() (contexte CLI / SSE).
    | On injecte donc les données via la configuration runtime.
    |
    */
        Config::set('installer.admin', [
            'username'   => $data['admin_username'] ?? null,
            'email'      => $data['admin_email'] ?? null,
            'password'   => $data['admin_password'] ?? null,
            'first_name' => $data['admin_firstname'] ?? null,
            'last_name'  => $data['admin_lastname'] ?? null,
        ]);
        /*
    | Les infos admin_* proviennent de l'étape 4 et sont stockées en session.
    | Le seeder doit récupérer ces valeurs (ex: via config(), cache(), ou DB).
    |
    */
        $emit(85, 'Création du compte Super Administrateur...');
        Artisan::call('db:seed', [
            '--class' => SuperAdminSeeder::class,
            '--force' => true,
        ]);

        /*
    |----------------------------------------------------------------------
    | 9) Finalisation : APP_INSTALLED=true
    |----------------------------------------------------------------------
    */
        $emit(95, 'Finalisation de l’installation...');
        app(EnvWriter::class)->markInstalled();

        $emit(98, 'Nettoyage final des caches...');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        $emit(100, 'Installation terminée.');
    }
    protected function configureDatabaseConnection(array $data): void
    {
        $connection = $data['db_connection'] ?? config('database.default');

        Config::set('database.default', $connection);

        Config::set("database.connections.{$connection}.host", $data['db_host'] ?? '127.0.0.1');
        Config::set("database.connections.{$connection}.port", $data['db_port'] ?? 3306);
        Config::set("database.connections.{$connection}.database", $data['db_database'] ?? null);
        Config::set("database.connections.{$connection}.username", $data['db_username'] ?? null);
        Config::set("database.connections.{$connection}.password", $data['db_password'] ?? '');

        // Purge/reconnect pour prendre en compte la config runtime
        DB::purge($connection);
        DB::reconnect($connection);
    }
}
