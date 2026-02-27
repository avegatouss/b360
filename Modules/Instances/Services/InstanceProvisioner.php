<?php

namespace Modules\Instances\Services;

use App\Instances\Instance;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

final class InstanceProvisioner
{
    public function provision(array $data): Instance
    {
        $isDedicated = ($data['db_mode'] ?? 'shared') === 'dedicated';
        $database = $isDedicated ? $data['database'] : null;

        $instance = Instance::query()->on('system')->create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'domain' => $data['domain'] ?? null,
            'subdomain' => $data['subdomain'] ?? null,
            'database' => $database,
            'db_driver' => $isDedicated ? config('database.connections.system.driver', 'mysql') : null,
            'is_active' => $data['is_active'] ?? true,
            'installed_at' => now(),
            'meta' => [],
        ]);

        if ($isDedicated && $database) {
            $this->createDatabase($database);
            $this->runMigrations($database);
        }

        // Auto-membership pour le super-admin courant
        $user = auth()->user();
        if ($user) {
            DB::connection('system')->table('instance_user')->insert([
                'instance_id' => $instance->id,
                'user_id' => $user->id,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Log::info('Instance provisionnée', [
            'instance_id' => $instance->id,
            'slug' => $instance->slug,
            'db_mode' => $isDedicated ? 'dedicated' : 'shared',
        ]);

        return $instance;
    }

    private function createDatabase(string $database): void
    {
        $database = trim($database);

        if ($database === '' || strlen($database) > 64 || !preg_match('/^[a-zA-Z0-9_]+$/', $database)) {
            throw new RuntimeException('Nom de base de données invalide.');
        }

        try {
            DB::connection('system')->statement(
                "CREATE DATABASE IF NOT EXISTS `{$database}`
                 CHARACTER SET utf8mb4
                 COLLATE utf8mb4_unicode_ci"
            );
        } catch (\Throwable $e) {
            Log::error('Échec création base de données', [
                'database' => $database,
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException(
                "Impossible de créer la base de données « {$database} ». Vérifiez les droits MySQL."
            );
        }
    }

    private function runMigrations(string $database): void
    {
        $system = Config::get('database.connections.system');

        Config::set('database.connections.instance', array_replace($system, [
            'database' => $database,
        ]));

        DB::purge('instance');
        DB::reconnect('instance');

        $exitCode = Artisan::call('migrate', [
            '--database' => 'instance',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            Log::warning('Migrations avec avertissements', ['database' => $database, 'exit_code' => $exitCode]);
        }
    }
}
