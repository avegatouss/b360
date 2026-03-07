<?php

namespace Modules\Core\Tests\Unit;

use Carbon\Carbon;
use Modules\Core\Models\License;
use Modules\Core\Services\LicenseManager;
use Modules\Core\Tests\TestCase;

final class LicenseManagerTest extends TestCase
{
    private LicenseManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = app(LicenseManager::class);
    }

    public function test_issue_creates_license(): void
    {
        $license = $this->manager->issue(1);

        $this->assertInstanceOf(License::class, $license);
        $this->assertSame(1, $license->instance_id);
        $this->assertSame('standard', $license->type);
        $this->assertSame('active', $license->status);
        $this->assertDatabaseHas('licenses', ['instance_id' => 1]);
    }

    public function test_issue_generates_unique_key(): void
    {
        $license = $this->manager->issue(1);

        $this->assertMatchesRegularExpression('/^B360-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}$/', $license->license_key);
    }

    public function test_verify_valid_license(): void
    {
        $license = $this->manager->issue(1);

        $result = $this->manager->verify($license->license_key);

        $this->assertTrue($result['valid']);
        $this->assertSame(1, $result['instance_id']);
        $this->assertSame('standard', $result['type']);
    }

    public function test_verify_invalid_key(): void
    {
        $result = $this->manager->verify('INVALID-KEY');

        $this->assertFalse($result['valid']);
    }

    public function test_verify_expired_license(): void
    {
        $license = $this->manager->issue(1, 'standard', Carbon::now()->subDay());

        $result = $this->manager->verify($license->license_key);

        $this->assertFalse($result['valid']);
    }

    public function test_verify_updates_last_verified_at(): void
    {
        $license = $this->manager->issue(1);
        $this->assertNull($license->last_verified_at);

        $this->manager->verify($license->license_key);

        $license->refresh();
        $this->assertNotNull($license->last_verified_at);
    }

    public function test_revoke(): void
    {
        $this->manager->issue(1);

        $result = $this->manager->revoke(1);

        $this->assertTrue($result);
        $this->assertDatabaseHas('licenses', ['instance_id' => 1, 'status' => 'revoked']);
    }

    public function test_suspend(): void
    {
        $this->manager->issue(1);

        $result = $this->manager->suspend(1);

        $this->assertTrue($result);
        $this->assertDatabaseHas('licenses', ['instance_id' => 1, 'status' => 'suspended']);
    }

    public function test_reactivate(): void
    {
        $this->manager->issue(1);
        $this->manager->suspend(1);

        $result = $this->manager->reactivate(1);

        $this->assertTrue($result);
        $this->assertDatabaseHas('licenses', ['instance_id' => 1, 'status' => 'active']);
    }

    public function test_is_valid(): void
    {
        $this->manager->issue(1);

        $this->assertTrue($this->manager->isValid(1));
        $this->assertFalse($this->manager->isValid(999));
    }
}
