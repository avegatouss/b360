<?php

namespace Modules\Eshop360\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Eshop360\Services\ChannelAccessService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user is a member of the resolved channel.
 * Instance-admins bypass this check.
 */
final class ChannelMember
{
    public function __construct(
        private readonly ChannelAccessService $access,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $channel = $request->resolved_channel;

        if (!$channel) {
            abort(404);
        }

        $user = $request->user();

        if (!$user) {
            abort(403, 'You are not a member of this channel');
        }

        if (!$this->access->canAccessChannel($user, $channel)) {
            abort(403, 'You are not a member of this channel');
        }

        $request->merge([
            'channel_user_role' => $this->access->roleForChannel($user, $channel) ?? 'viewer',
        ]);

        return $next($request);
    }
}
