<?php

namespace Modules\Eshop360\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Support\CurrentChannel;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves a DistributionChannel from the {channel} route parameter (ID or slug).
 * Ensures the channel belongs to the current instance and is active.
 */
final class ResolveChannel
{
    public function handle(Request $request, Closure $next): Response
    {
        $channelParam = $request->route('channel');

        if (!$channelParam) {
            abort(404);
        }

        $instance = CurrentInstance::get();

        $query = DistributionChannel::query()
            ->where('is_active', true);

        if ($instance) {
            $query->where('instance_id', $instance->id);
        }

        $channel = is_numeric($channelParam)
            ? $query->find($channelParam)
            : $query->where('slug', $channelParam)->first();

        if (!$channel) {
            abort(404);
        }

        $request->merge(['resolved_channel' => $channel]);

        // Set CurrentChannel so ChannelScope filters correctly for all users
        // (portal customers, channel operators, hub admins navigating into a channel)
        CurrentChannel::set($channel);

        return $next($request);
    }
}
