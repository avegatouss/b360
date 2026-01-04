<?php

namespace Tests\Feature\Installer;

use Tests\TestCase;

class InstallerAccessTest extends TestCase
{
    /** @test */
    public function installer_is_accessible_when_app_not_installed(): void
    {
        config(['app.installed' => false]);

        $response = $this->get('/install');

        $response->assertStatus(200);
        $response->assertSee('Installation');
    }

    /** @test */
    public function installer_is_blocked_when_app_is_installed(): void
    {
        config(['app.installed' => true]);

        $response = $this->get('/install');

        $response->assertRedirect('/');
    }
}
