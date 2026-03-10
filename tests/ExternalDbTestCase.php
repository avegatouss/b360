<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;

abstract class ExternalDbTestCase extends TestCase
{
    protected static bool $migrated = false;

    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        $driver = env('TEST_DB_DRIVER');
        if (!$driver) {
            return;
        }

        $host = env('TEST_DB_HOST');
        $port = env('TEST_DB_PORT');
        $database = env('TEST_DB_DATABASE');
        $username = env('TEST_DB_USERNAME');
        $password = env('TEST_DB_PASSWORD');

        if (!$host || !$database || !$username) {
            return;
        }

        $config = [
            'driver' => $driver,
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'charset' => $driver === 'pgsql' ? 'utf8' : 'utf8mb4',
            'collation' => $driver === 'pgsql' ? 'utf8_unicode_ci' : 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ];

        $this->app['config']->set('database.default', $driver);
        $this->app['config']->set("database.connections.{$driver}", $config);
        $this->app['config']->set('database.connections.system', $config);

        $this->app['config']->set('permission.teams', true);
        $this->app['config']->set('permission.team_foreign_key', 'instance_id');
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (!env('TEST_DB_DRIVER')) {
            $this->markTestSkipped('External DB not configured (TEST_DB_DRIVER).');
        }

        if (!self::$migrated) {
            Artisan::call('migrate', ['--force' => true]);
            self::$migrated = true;
        }
    }
}
