<?php

namespace Modules\Installer\Tests\Feature\Installer;

use App\Installer\InstallLock;
use Modules\Installer\Tests\TestCase;

final class InstallerAccessTest extends TestCase
{
    public function test_installer_is_accessible_when_not_installed(): void
    {
        $this->get('/install')
            ->assertOk();
    }

    public function test_installer_is_hidden_when_installed(): void
    {
        config(['app.installed' => true]);

        $this->get('/install')
            ->assertStatus(404);
    }

    public function test_installer_returns_409_when_installing_lock_exists(): void
    {
        InstallLock::acquire('test-run');

        $this->get('/install/requirements')
            ->assertStatus(409);
    }
}
