<?php

namespace Modules\Installer\Tests\Unit;

use App\Installer\InstallLock;
use Modules\Installer\Tests\TestCase;

final class InstallLockTest extends TestCase
{
    public function test_acquire_and_release_installing_lock(): void
    {
        $this->assertFalse(InstallLock::isInstalling());

        InstallLock::acquire('run-1');
        $this->assertTrue(InstallLock::isInstalling());

        InstallLock::releaseInstalling();
        $this->assertFalse(InstallLock::isInstalling());
    }

    public function test_mark_installed_creates_installed_lock_and_clears_installing(): void
    {
        InstallLock::acquire('run-2');
        $this->assertTrue(InstallLock::isInstalling());

        InstallLock::markInstalled('run-2');

        $this->assertFalse(InstallLock::isInstalling());
        $this->assertTrue(InstallLock::isInstalled());
    }
}
