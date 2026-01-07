<?php

namespace Modules\Core\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Modules\Core\Providers\CoreServiceProvider;
use Modules\Core\Providers\CoreAuthServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            CoreServiceProvider::class,
            CoreAuthServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // Use sqlite memory by default for tests; map "system" to default
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['config']->set('database.connections.system', $app['config']->get('database.connections.sqlite'));

        // Spatie teams
        $app['config']->set('permission.teams', true);
        $app['config']->set('permission.team_foreign_key', 'instance_id');
    }

    protected function defineDatabaseMigrations(): void
    {
        // Base tables minimal for tests
        $this->loadLaravelMigrations(['--database' => 'system']);

        // If your app already has instances/users migrations, you can remove these and load your real ones.
        $this->artisan('migrate', ['--database' => 'system'])->run();

        // Core migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Spatie migrations (point to vendor if present; else ensure they exist in your app)
        // In real repo, you should run vendor:publish and use your app migrations.
    }
}
