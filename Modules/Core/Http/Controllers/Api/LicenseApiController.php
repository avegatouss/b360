<?php

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Models\License;
use Modules\Core\Services\LicenseManager;

final class LicenseApiController extends Controller
{
    public function __construct(
        private readonly LicenseManager $licenseManager
    ) {}

    public function verify(Request $request): JsonResponse
    {
        $enabled = function_exists('setting')
            ? setting('license.verification_enabled', false)
            : false;

        if (!$enabled) {
            return response()->json([
                'error' => 'License verification is disabled',
            ], 503);
        }

        $request->validate([
            'license_key' => 'required|string|max:64',
        ]);

        $result = $this->licenseManager->verify($request->input('license_key'));

        return response()->json($result);
    }

    public function status(int $instanceId): JsonResponse
    {
        $license = License::where('instance_id', $instanceId)->first();

        if (!$license) {
            return response()->json(['error' => 'No license found'], 404);
        }

        return response()->json([
            'instance_id' => $license->instance_id,
            'type' => $license->type,
            'status' => $license->status,
            'issued_at' => $license->issued_at?->toIso8601String(),
            'expires_at' => $license->expires_at?->toIso8601String(),
            'last_verified_at' => $license->last_verified_at?->toIso8601String(),
            'valid' => $license->isValid(),
        ]);
    }
}
