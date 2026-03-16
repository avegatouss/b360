<?php

namespace Modules\Billing\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Billing\Services\FeatureRegistry;
use Modules\Core\Support\CurrentInstance;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that blocks access to features not available in the current plan.
 *
 * Usage in routes: ->middleware('billing.feature:eshop360.channels')
 *
 * If the feature is not available:
 * - JSON requests get a 403 with upgrade URL
 * - Web requests are redirected to the upgrade page
 */
final class EnsureFeature
{
    public function __construct(
        private readonly FeatureRegistry $featureRegistry,
    ) {}

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $instance = CurrentInstance::get();

        if (!$instance) {
            abort(503, 'No instance context.');
        }

        // Super-admins bypass all feature gates
        if (auth()->check() && auth()->user()->hasRole('super-admin', 0)) {
            return $next($request);
        }

        if ($this->featureRegistry->has($feature, $instance->id)) {
            return $next($request);
        }

        $upgradeUrl = route('billing.upgrade', [
            'slug' => $instance->slug,
            'feature' => $feature,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'feature_unavailable',
                'feature' => $feature,
                'message' => "Cette fonctionnalite necessite une mise a niveau de votre abonnement.",
                'upgrade_url' => $upgradeUrl,
            ], 403);
        }

        return redirect($upgradeUrl)
            ->with('warning', "Cette fonctionnalite necessite une mise a niveau de votre abonnement.");
    }
}
