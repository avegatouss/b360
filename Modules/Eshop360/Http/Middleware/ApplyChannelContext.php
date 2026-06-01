<?php

namespace Modules\Eshop360\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Modules\Eshop360\Support\CurrentChannel;

/**
 * Reads the current channel from session and shares it with all views.
 *
 * View::share is deferred to AFTER the request pipeline so that
 * ResolveChannel middleware (which runs later) can call CurrentChannel::set()
 * before views are rendered.
 */
class ApplyChannelContext
{
    public function handle(Request $request, Closure $next)
    {
        CurrentChannel::flush();

        // Pre-load channel from session (before ResolveChannel may override)
        $channel = CurrentChannel::get();

        // Force channel_id in the request when a scoped channel is active.
        if (CurrentChannel::isScoped()) {
            $request->query->set('channel_id', $channel->id);
            $request->request->set('channel_id', $channel->id);
        }

        $response = $next($request);

        // Share channel context AFTER the full middleware pipeline has run
        // (ResolveChannel may have called CurrentChannel::set() with a different channel)
        View::share('currentChannel', CurrentChannel::get());
        View::share('isChannelScoped', CurrentChannel::isScoped());

        return $response;
    }
}
