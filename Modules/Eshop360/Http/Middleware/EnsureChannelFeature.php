<?php

namespace Modules\Eshop360\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Eshop360\Services\ChannelAccessService;
use Modules\Eshop360\Services\EshopSettingsService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Checks that a feature is enabled on the resolved channel.
 *
 * Usage: ->middleware('eshop.channel.feature:hr')
 *
 * Hub admins (Saphir Plus) bypass — they always have access.
 * If no channel is resolved (hub-level routes), the request passes through.
 */
final class EnsureChannelFeature
{
    public function __construct(
        private readonly EshopSettingsService $settings,
        private readonly ChannelAccessService $accessService,
    ) {}

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $channel = $request->resolved_channel;

        // No channel context (hub-level route) — allow through
        if (!$channel) {
            return $next($request);
        }

        // Hub admins bypass feature restrictions
        if ($this->accessService->isHubAdmin(auth()->user())) {
            return $next($request);
        }

        if (!$this->settings->isChannelFeatureEnabled($feature, $channel->id, true)) {
            abort(403, __('Cette fonctionnalite est desactivee pour ce canal.'));
        }

        return $next($request);
    }
}
