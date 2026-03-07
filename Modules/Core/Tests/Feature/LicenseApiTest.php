<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Models\License;
use Modules\Core\Services\LicenseManager;
use Modules\Core\Tests\TestCase;

final class LicenseApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Disable throttle middleware for API tests
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    public function test_verify_returns_valid(): void
    {
        $this->enableLicenseVerification();

        $license = app(LicenseManager::class)->issue(1);

        $this->postJson('/api/license/verify', [
            'license_key' => $license->license_key,
        ])
            ->assertOk()
            ->assertJsonFragment(['valid' => true]);
    }

    public function test_verify_returns_invalid_for_bad_key(): void
    {
        $this->enableLicenseVerification();

        $this->postJson('/api/license/verify', [
            'license_key' => 'FAKE-KEY',
        ])
            ->assertOk()
            ->assertJsonFragment(['valid' => false]);
    }

    public function test_verify_returns_503_when_disabled(): void
    {
        $this->postJson('/api/license/verify', [
            'license_key' => 'ANY-KEY',
        ])
            ->assertStatus(503);
    }

    public function test_status_requires_auth(): void
    {
        $this->getJson('/api/license/status/1')
            ->assertUnauthorized();
    }

    public function test_status_returns_license_data(): void
    {
        $license = app(LicenseManager::class)->issue(1);

        $user = \App\Models\User::create([
            'full_name' => 'Api User',
            'email' => 'api@test.com',
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->getJson('/api/license/status/1')
            ->assertOk()
            ->assertJsonFragment([
                'instance_id' => 1,
                'status' => 'active',
                'valid' => true,
            ]);
    }

    private function enableLicenseVerification(): void
    {
        \Illuminate\Support\Facades\DB::connection('system')->table('settings')->insert([
            'instance_id' => 0,
            'group' => 'license',
            'key' => 'verification_enabled',
            'value' => '1',
            'type' => 'boolean',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
