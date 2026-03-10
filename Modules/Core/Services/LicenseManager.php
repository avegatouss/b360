<?php

namespace Modules\Core\Services;

use Carbon\Carbon;
use Modules\Core\Models\License;

final class LicenseManager
{
    public function issue(int $instanceId, string $type = 'standard', ?Carbon $expiresAt = null): License
    {
        return License::create([
            'instance_id' => $instanceId,
            'license_key' => $this->generateKey(),
            'type' => $type,
            'status' => 'active',
            'issued_at' => now(),
            'expires_at' => $expiresAt,
        ]);
    }

    public function verify(string $licenseKey): array
    {
        $license = License::where('license_key', $licenseKey)->first();

        if (!$license) {
            return ['valid' => false];
        }

        $license->update(['last_verified_at' => now()]);

        if (!$license->isValid()) {
            return [
                'valid' => false,
                'instance_id' => $license->instance_id,
                'status' => $license->status,
            ];
        }

        return [
            'valid' => true,
            'instance_id' => $license->instance_id,
            'type' => $license->type,
            'expires_at' => $license->expires_at?->toIso8601String(),
        ];
    }

    public function revoke(int $instanceId): bool
    {
        return $this->updateStatus($instanceId, 'revoked');
    }

    public function suspend(int $instanceId): bool
    {
        return $this->updateStatus($instanceId, 'suspended');
    }

    public function reactivate(int $instanceId): bool
    {
        return $this->updateStatus($instanceId, 'active');
    }

    public function isValid(int $instanceId): bool
    {
        $license = License::where('instance_id', $instanceId)->first();

        return $license?->isValid() ?? false;
    }

    public function generateKey(): string
    {
        $segments = [];
        for ($i = 0; $i < 4; $i++) {
            $segments[] = strtoupper(bin2hex(random_bytes(2)));
        }

        return 'B360-' . implode('-', $segments);
    }

    private function updateStatus(int $instanceId, string $status): bool
    {
        $license = License::where('instance_id', $instanceId)->first();

        if (!$license) {
            return false;
        }

        return $license->update(['status' => $status]);
    }
}
