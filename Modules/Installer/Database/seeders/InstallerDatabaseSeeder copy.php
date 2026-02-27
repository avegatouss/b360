<?php

namespace Modules\Installer\Services;

use App\Instances\DatabaseCreator;
use Database\Seeders\InstanceSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class InstallerRunner
{
    public function run(array $data, ?callable $progress = null): void
    {
        if (config('app.installed', false) === true) {
            throw new RuntimeException('Application déjà installée.');
        }

        $emit = function (int $percent, string $message) use ($progress): void {
            if ($progress) $progress($percent, $message);
        };

        $envPath = base_path('.env');

        try {
            $emit(10, 'Écriture du fichier .env...');
            app(EnvWriter::class)->write($data); // doit écrire APP_INSTALLED=false

            $emit(18, 'Nettoyage du cache configuration...');
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            $emit(25, 'Initialisation de la connexion base de données...');
            $this->configureDatabaseConnection($data);

            try {
                DB::connection()->getPdo();
            } catch (\Throwable) {
                throw new RuntimeException('Connexion à la base de données impossible.');
            }

            $emit(40, 'Exécution des migrations...');
            Artisan::call('migrate', ['--force' => true]);

            // Préparation future : shared vs database-per-instance
            $strategy = $data['instance_db_strategy'] ?? 'database-per-instance';

            if (($data['instance_mode'] ?? 'single') === 'multi' && $strategy === 'database-per-instance') {
                $emit(55, 'Création de la base de données de l’instance...');
                $databaseName = $this->buildInstanceDatabaseName($data);
                app(DatabaseCreator::class)->create($databaseName);
            }

            $emit(70, 'Génération des données Instance...');
            Artisan::call('db:seed', [
                '--class' => InstanceSeeder::class,
                '--force' => true,
            ]);

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

            $emit(98, 'Nettoyage final des caches...');
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            $emit(100, 'Installation terminée.');
        } catch (\Throwable $e) {

            // Rollback / trace .env si l'installation n'est pas finalisée
            if (file_exists($envPath)) {
                @rename($envPath, $envPath . '.failed.' . date('YmdHis'));
            }

            throw $e;
        }
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

        DB::purge($connection);
        DB::reconnect($connection);
    }

    private function buildInstanceDatabaseName(array $data): string
    {
        $slug = Str::slug($data['app_name'] ?? 'b360', '_');

        $name = ($data['db_prefix'] ?? '') . $slug . ($data['db_suffix'] ?? '');

        // MySQL max 64 (adaptable si tu élargis driver)
        $name = substr($name, 0, 64);

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
            throw new RuntimeException('Nom DB instance invalide.');
        }

        return $name;
    }
}
