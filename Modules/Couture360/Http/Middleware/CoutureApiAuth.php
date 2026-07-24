<?php

declare(strict_types=1);

namespace Modules\Couture360\Http\Middleware;

use App\Instances\Instance;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Couture360\Domain\Auth\Services\DeviceTokenService;
use Symfony\Component\HttpFoundation\Response;

final class CoutureApiAuth
{
    public function __construct(private readonly DeviceTokenService $tokens) {}

    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();
        if (! $bearer) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        $token = $this->tokens->resolve($bearer);
        if (! $token) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        $user = User::find($token->user_id);
        $instance = Instance::find($token->instance_id);
        if (! $user || ! $instance) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        // Re-verify membership (a user may have been removed from the instance
        // since the token was issued).
        $isMember = DB::connection('system')->table('instance_user')
            ->where('user_id', $user->id)
            ->where('instance_id', $instance->id)
            ->exists();
        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');
        if (! $isMember && ! $isSuperAdmin) {
            return response()->json(['error' => 'forbidden'], 403);
        }

        CurrentInstance::set($instance);
        $request->setUserResolver(fn () => $user);
        $request->attributes->set('couture_device_token', $token);

        return $next($request);
    }
}
