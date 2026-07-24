<?php

declare(strict_types=1);

namespace Modules\Couture360\Domain\Auth\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Modules\Couture360\Domain\Auth\Models\CoutureDeviceToken;

final class DeviceTokenService
{
    public function hash(string $plainText): string
    {
        return hash('sha256', $plainText);
    }

    public function issue(int $userId, int $instanceId, string $deviceName): string
    {
        $plain = Str::random(48);

        $ttlDays = (int) config('couture360.api.token_ttl_days', 90);

        CoutureDeviceToken::query()->create([
            'instance_id' => $instanceId,
            'user_id' => $userId,
            'name' => $deviceName,
            'token_hash' => $this->hash($plain),
            'expires_at' => $ttlDays > 0 ? Carbon::now()->addDays($ttlDays) : null,
        ]);

        return $plain;
    }

    public function resolve(string $plainText): ?CoutureDeviceToken
    {
        $token = CoutureDeviceToken::query()
            ->where('token_hash', $this->hash($plainText))
            ->first();

        if (! $token) {
            return null;
        }

        if ($token->expires_at !== null && $token->expires_at->isPast()) {
            return null;
        }

        $token->forceFill(['last_used_at' => Carbon::now()])->save();

        return $token;
    }

    public function revoke(CoutureDeviceToken $token): void
    {
        $token->delete();
    }
}
