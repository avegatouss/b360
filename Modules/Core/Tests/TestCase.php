<?php

namespace Modules\Core\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\TeamContext;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        // Configure system as sqlite so migrations targeting 'system' use SQLite
        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite.database', ':memory:');
        $this->app['config']->set('database.connections.system', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        $this->app['config']->set('permission.teams', true);
        $this->app['config']->set('permission.team_foreign_key', 'instance_id');
        $this->app['config']->set('app.installed', true);

        // Share PDO before migrations run (so Schema::connection('system') creates tables in same DB)
        $this->sharePdo();
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Re-share PDO after RefreshDatabase may have reconnected
        $this->sharePdo();

        // Reset Spatie team/permission state so suite order cannot leak into tests.
        // forgetCachedPermissions() clears both the in-memory registrar state
        // and the cache backend entry. Necessary in parallel mode where the
        // array cache driver is shared by sequential test classes within a
        // worker process — without it, ghost cache entries from rolled-back
        // rows produce intermittent "There is no permission named X" errors.
        TeamContext::clear();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function sharePdo(): void
    {
        $pdo = DB::connection('sqlite')->getPdo();
        DB::connection('system')->setPdo($pdo)->setReadPdo($pdo);
    }
}
