<?php

namespace Tests\Feature\Installer;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class InstallerFlowTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        // Nettoyage .env de test
        if (File::exists(base_path('.env'))) {
            File::delete(base_path('.env'));
        }
    }

    /** @test */
    public function installation_fails_if_database_is_invalid(): void
    {
        config(['app.installed' => false]);

        $payload = [
            'app_name' => 'B360 Test',
            'app_url' => 'http://b360.test',
            'timezone' => 'UTC',
            'locale' => 'fr',

            'db_host' => 'invalid-host',
            'db_port' => 3306,
            'db_database' => 'fake',
            'db_username' => 'fake',
            'db_password' => 'fake',

            'instance_mode' => 'single',

            'admin_firstname' => 'Admin',
            'admin_lastname' => 'Test',
            'admin_username' => 'admin',
            'admin_password' => 'password123',
        ];

        $response = $this->post('/install', $payload);

        $response->assertSessionHasErrors('install');

        // Très important : APP_INSTALLED ne doit PAS être true
        $this->assertFalse(config('app.installed'));
    }
    /** @test */
public function installation_completes_successfully(): void
{
    config(['app.installed' => false]);

    $payload = [
        'app_name' => 'B360',
        'app_url' => 'http://b360.test',
        'timezone' => 'UTC',
        'locale' => 'fr',

        'db_host' => 'localhost',
        'db_port' => 3306,
        'db_database' => ':memory:',
        'db_username' => 'root',
        'db_password' => '',

        'instance_mode' => 'single',

        'admin_firstname' => 'Admin',
        'admin_lastname' => 'Root',
        'admin_username' => 'admin',
        'admin_password' => 'password123',
    ];

    $response = $this->post('/install', $payload);

    $response->assertRedirect('/login');
}

}
