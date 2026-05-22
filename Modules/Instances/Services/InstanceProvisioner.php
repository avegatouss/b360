<?php

namespace Modules\Instances\Services;

use App\Instances\Instance;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

final class InstanceProvisioner
{
    public function provision(array $data): Instance
    {
        $strategy = config('app.instance_db_strategy', 'shared');
        $isDedicated = $strategy === 'database-per-instance';
        $database = $isDedicated ? $this->resolveDatabaseName($data['slug'], $data['database'] ?? null) : null;

        $instance = Instance::create([
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
            $this->runInstanceMigrations($database);
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

        Log::info('Instance provisionnee', [
            'instance_id' => $instance->id,
            'slug' => $instance->slug,
            'database' => $database,
            'db_mode' => $isDedicated ? 'dedicated' : 'shared',
        ]);

        return $instance;
    }

    /**
     * Generate database name from config prefix/suffix + slug.
     * Falls back to explicit name if provided.
     */
    private function resolveDatabaseName(string $slug, ?string $explicitName = null): string
    {
        if ($explicitName) {
            return $explicitName;
        }

        $prefix = config('app.instance_db_prefix', '');
        $suffix = config('app.instance_db_suffix', '');
        $base = Str::replace('-', '_', $slug);

        return $prefix . $base . $suffix;
    }

    private function createDatabase(string $database): void
    {
        $database = trim($database);

        if ($database === '' || strlen($database) > 64 || !preg_match('/^[a-zA-Z0-9_]+$/', $database)) {
            throw new RuntimeException('Nom de base de donnees invalide.');
        }

        try {
            DB::connection('system')->statement(
                "CREATE DATABASE IF NOT EXISTS `{$database}`
                 CHARACTER SET utf8mb4
                 COLLATE utf8mb4_unicode_ci"
            );
        } catch (\Throwable $e) {
            Log::error('Echec creation base de donnees', [
                'database' => $database,
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException(
                "Impossible de creer la base de donnees [{$database}]. Verifiez les droits MySQL."
            );
        }
    }

    /**
     * Run only instance-scoped migrations (not system migrations).
     *
     * System tables (users, instances, permissions, modules...) live in the
     * system DB and their migrations hardcode Schema::connection('system').
     * Running them again would cause "table already exists" errors.
     *
     * Instance migrations should be placed in each module's
     * Database/InstanceMigrations/ directory.
     */
    private function runInstanceMigrations(string $database): void
    {
        $system = Config::get('database.connections.system');

        Config::set('database.connections.instance', array_replace($system, [
            'database' => $database,
        ]));

        DB::purge('instance');
        DB::reconnect('instance');

        $paths = $this->collectInstanceMigrationPaths();

        if (empty($paths)) {
            Log::info('Aucune migration instance a executer', ['database' => $database]);
            return;
        }

        foreach ($paths as $path) {
            $exitCode = Artisan::call('migrate', [
                '--database' => 'instance',
                '--path' => $path,
                '--realpath' => true,
                '--force' => true,
            ]);

            if ($exitCode !== 0) {
                Log::warning('Migrations avec avertissements', [
                    'database' => $database,
                    'path' => $path,
                    'exit_code' => $exitCode,
                ]);
            }
        }
    }

    /**
     * Collect migration paths intended for instance databases.
     * Convention: Modules/{Name}/Database/InstanceMigrations/
     */
    private function collectInstanceMigrationPaths(): array
    {
        $paths = [];
        $modulesDir = base_path('Modules');

        if (!is_dir($modulesDir)) {
            return $paths;
        }

        foreach (glob($modulesDir . '/*/Database/InstanceMigrations') as $path) {
            if (is_dir($path) && glob($path . '/*.php')) {
                $paths[] = $path;
            }
        }

        return $paths;
    }
}
