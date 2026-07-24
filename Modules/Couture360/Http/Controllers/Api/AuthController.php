<?php

declare(strict_types=1);

namespace Modules\Couture360\Http\Controllers\Api;

use App\Instances\Instance;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Couture360\Domain\Auth\Models\CoutureDeviceToken;
use Modules\Couture360\Domain\Auth\Services\DeviceTokenService;

final class AuthController extends Controller
{
    public function __construct(private readonly DeviceTokenService $tokens) {}

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'instance_id' => ['required'],
            'device_name' => ['required', 'string', 'max:120'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['error' => 'invalid_credentials'], 401);
        }

        $instance = Instance::find($data['instance_id']);
        if (! $instance) {
            return response()->json(['error' => 'instance_not_found'], 404);
        }

        $isMember = DB::connection('system')->table('instance_user')
            ->where('user_id', $user->id)
            ->where('instance_id', $instance->id)
            ->exists();
        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');
        if (! $isMember && ! $isSuperAdmin) {
            return response()->json(['error' => 'forbidden'], 403);
        }

        $plain = $this->tokens->issue((int) $user->id, (int) $instance->id, $data['device_name']);

        return response()->json([
            'token' => $plain,
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
            'instance_id' => (int) $instance->id,
            'permissions' => $this->permissionsFor($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var CoutureDeviceToken $token */
        $token = $request->attributes->get('couture_device_token');

        return response()->json([
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
            'instance_id' => (int) $token->instance_id,
            'permissions' => $this->permissionsFor($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var CoutureDeviceToken|null $token */
        $token = $request->attributes->get('couture_device_token');
        if ($token) {
            $this->tokens->revoke($token);
        }

        return response()->json(null, 204);
    }

    /** @return array<int, string> */
    private function permissionsFor(User $user): array
    {
        return $user->getAllPermissions()->pluck('name')->values()->all();
    }
}
