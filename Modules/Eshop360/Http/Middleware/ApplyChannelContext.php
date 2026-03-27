<?php

namespace Modules\Eshop360\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Modules\Eshop360\Support\CurrentChannel;

/**
 * Reads the current channel from session and shares it with all views.
 *
 * Also merges channel_id into the request if a scoped channel is active
 * and the request doesn't already carry one — so existing controllers
 * that read $request->channel_id automatically filter by the active channel.
 */
class ApplyChannelContext
{
    public function handle(Request $request, Closure $next)
    {
        CurrentChannel::flush();

        $channel = CurrentChannel::get();

        // Share channel context to all views
        View::share('currentChannel', $channel);
        View::share('isChannelScoped', CurrentChannel::isScoped());

        // Force channel_id in the request when a scoped channel is active.
        // Uses query->set() and request->set() to override any user-supplied value
        // (merge() alone can be bypassed via URL ?channel_id=X)
        if (CurrentChannel::isScoped()) {
            $request->query->set('channel_id', $channel->id);
            $request->request->set('channel_id', $channel->id);
        }

        return $next($request);
    }
}
