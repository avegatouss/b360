<?php

namespace Modules\Installer\Tests\Feature\Installer;

use Modules\Installer\Tests\TestCase;

final class InstallerDatabaseSuccessTest extends TestCase
{
    private function mysqlConfig(): ?array
    {
        $host = env('TEST_MYSQL_HOST');
        $db = env('TEST_MYSQL_DATABASE');
        $user = env('TEST_MYSQL_USERNAME');
        $pass = env('TEST_MYSQL_PASSWORD', '');
        $port = (int) (env('TEST_MYSQL_PORT') ?: 3306);

        if (!$host || !$db || !$user) {
            return null;
        }

        return compact('host', 'db', 'user', 'pass', 'port');
    }

    private function pgsqlConfig(): ?array
    {
        $host = env('TEST_PGSQL_HOST');
        $db = env('TEST_PGSQL_DATABASE');
        $user = env('TEST_PGSQL_USERNAME');
        $pass = env('TEST_PGSQL_PASSWORD', '');
        $port = (int) (env('TEST_PGSQL_PORT') ?: 5432);

        if (!$host || !$db || !$user) {
            return null;
        }

        return compact('host', 'db', 'user', 'pass', 'port');
    }

    public function test_database_test_succeeds_with_mysql_when_available(): void
    {
        $cfg = $this->mysqlConfig();
        if (!$cfg) {
            $this->markTestSkipped('MySQL test DB not configured (TEST_MYSQL_*).');
        }

        $this->withSession(['installer.steps' => [1 => true]]);

        $response = $this->postJson('/install/database/test', [
            'environment_mode' => 'demo',
            'db_connection' => 'mysql',
            'db_host' => $cfg['host'],
            'db_port' => $cfg['port'],
            'db_database' => $cfg['db'],
            'db_username' => $cfg['user'],
            'db_password' => $cfg['pass'],
            'create_database' => false,
        ]);

        $response->assertOk()
            ->assertJsonFragment(['ok' => true])
            ->assertJsonFragment(['db_confirmed' => true]);
    }

    public function test_database_test_succeeds_with_pgsql_when_available(): void
    {
        $cfg = $this->pgsqlConfig();
        if (!$cfg) {
            $this->markTestSkipped('PostgreSQL test DB not configured (TEST_PGSQL_*).');
        }

        $this->withSession(['installer.steps' => [1 => true]]);

        $response = $this->postJson('/install/database/test', [
            'environment_mode' => 'demo',
            'db_connection' => 'pgsql',
            'db_host' => $cfg['host'],
            'db_port' => $cfg['port'],
            'db_database' => $cfg['db'],
            'db_username' => $cfg['user'],
            'db_password' => $cfg['pass'],
            'create_database' => false,
        ]);

        $response->assertOk()
            ->assertJsonFragment(['ok' => true])
            ->assertJsonFragment(['db_confirmed' => true]);
    }
}
