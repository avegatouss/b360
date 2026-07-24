<?php

declare(strict_types=1);

namespace Modules\Couture360\Tests\Feature;

use Modules\Couture360\Domain\Auth\Models\CoutureDeviceToken;
use Modules\Couture360\Domain\Auth\Services\DeviceTokenService;
use Modules\Couture360\Tests\TestCase;

final class DeviceTokenServiceTest extends TestCase
{
    private function svc(): DeviceTokenService
    {
        return app(DeviceTokenService::class);
    }

    public function test_issues_plaintext_and_stores_only_the_hash(): void
    {
        $instance = $this->makeInstance();
        $user = $this->makeMember($instance);

        $plain = $this->svc()->issue((int) $user->id, (int) $instance->id, 'Pixel-7');

        $this->assertGreaterThanOrEqual(40, strlen($plain));

        $row = CoutureDeviceToken::query()->first();
        $this->assertNotNull($row);
        $this->assertSame(hash('sha256', $plain), $row->token_hash);
        $this->assertNotSame($plain, $row->token_hash);
        $this->assertSame((int) $user->id, (int) $row->user_id);
        $this->assertSame((int) $instance->id, (int) $row->instance_id);
    }

    public function test_resolves_a_valid_token_and_touches_last_used_at(): void
    {
        $instance = $this->makeInstance();
        $user = $this->makeMember($instance);

        $plain = $this->svc()->issue((int) $user->id, (int) $instance->id, 'Pixel-7');
        $token = $this->svc()->resolve($plain);

        $this->assertNotNull($token);
        $this->assertSame((int) $user->id, (int) $token->user_id);
        $this->assertNotNull($token->last_used_at);
    }

    public function test_returns_null_for_unknown_token(): void
    {
        $this->assertNull($this->svc()->resolve('not-a-real-token'));
    }

    public function test_returns_null_for_expired_token(): void
    {
        $instance = $this->makeInstance();
        $user = $this->makeMember($instance);

        $plain = $this->svc()->issue((int) $user->id, (int) $instance->id, 'Pixel-7');
        CoutureDeviceToken::query()->update(['expires_at' => now()->subDay()]);

        $this->assertNull($this->svc()->resolve($plain));
    }

    public function test_revokes_a_token(): void
    {
        $instance = $this->makeInstance();
        $user = $this->makeMember($instance);

        $plain = $this->svc()->issue((int) $user->id, (int) $instance->id, 'Pixel-7');
        $this->svc()->revoke($this->svc()->resolve($plain));

        $this->assertSame(0, CoutureDeviceToken::query()->count());
    }
}
