<?php

namespace Modules\Eshop360\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\DistributionChannel;
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

        return $next($request);
    }
}
