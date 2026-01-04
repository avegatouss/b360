<?php

namespace Tests\Feature\Installer;

use Tests\TestCase;

class InstallerValidationTest extends TestCase
{
    /** @test */
    public function installer_requires_mandatory_fields(): void
    {
        config(['app.installed' => false]);

        $response = $this->post('/install', []);

        $response->assertSessionHasErrors([
            'app_name',
            'app_url',
            'db_host',
            'db_database',
            'admin_username',
            'admin_password',
        ]);
    }
}
