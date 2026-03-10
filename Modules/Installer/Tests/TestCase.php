<?php

namespace Modules\Installer\Tests;

use App\Installer\InstallLock;
use Modules\Core\Tests\TestCase as CoreTestCase;

abstract class TestCase extends CoreTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.installed' => false]);
        InstallLock::forceCleanup();

        // Disable throttle middleware to avoid 429 when running full suite
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    protected function tearDown(): void
    {
        InstallLock::forceCleanup();
        parent::tearDown();
    }
}
