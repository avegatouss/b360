<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Auth\Models\IpRule;
use Modules\Core\Support\CurrentInstance;
use Symfony\Component\HttpFoundation\Response;

class CheckIpAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $instance = CurrentInstance::get();

        if (!$instance) {
            return $next($request);
        }

        $clientIp = $request->ip();
        $cacheKey = "ip_rules:{$instance->id}";

        $rules = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($instance) {
            return IpRule::withoutInstanceScope()
                ->where('instance_id', $instance->id)
                ->get(['id', 'ip_address', 'type', 'user_id']);
        });

        // 1. Global deny rules (no user_id)
        $globalDenyRules = $rules->where('type', 'deny')->whereNull('user_id');
        if ($globalDenyRules->where('ip_address', $clientIp)->isNotEmpty()) {
            abort(403, 'IP blocked.');
        }

        // 2. Whitelist mode: if allow rules exist, IP must match one
        $globalAllowRules = $rules->where('type', 'allow')->whereNull('user_id');
        if ($globalAllowRules->isNotEmpty() && $globalAllowRules->where('ip_address', $clientIp)->isEmpty()) {
            abort(403, 'IP not whitelisted.');
        }

        // 3. Per-user deny rules
        if ($request->user()) {
            $userDenyRules = $rules->where('type', 'deny')
                ->where('user_id', $request->user()->id);

            if ($userDenyRules->where('ip_address', $clientIp)->isNotEmpty()) {
                abort(403, 'IP blocked for your account.');
            }
        }

        return $next($request);
    }
}
