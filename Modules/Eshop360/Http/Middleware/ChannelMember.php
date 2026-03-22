<?php

namespace Modules\Eshop360\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user is a member of the resolved channel.
 * Instance-admins bypass this check.
 */
final class ChannelMember
{
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

        // Instance-admins bypass channel membership check
        if ($user->hasRole('instance-admin') || $user->hasRole('super-admin')) {
            $request->merge(['channel_user_role' => 'admin']);
            return $next($request);
        }

        $channelUser = $channel->channelUsers()->where('user_id', $user->id)->first();

        if (!$channelUser) {
            abort(403, 'You are not a member of this channel');
        }

        $request->merge(['channel_user_role' => $channelUser->role ?? 'viewer']);

        return $next($request);
    }
}
