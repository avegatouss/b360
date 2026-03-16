<?php

namespace Modules\Eshop360\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Eshop360\Services\FeatureGate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that blocks access to paid features when the instance
 * doesn't have the required feature in its subscription plan.
 *
 * Usage in routes: ->middleware('eshop.feature:channels')
 */
final class EnsurePaidFeature
{
    public function __construct(private FeatureGate $gate) {}

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (!$this->gate->has($feature)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'feature_unavailable',
                    'feature' => $feature,
                    'message' => __('eshop360::messages.feature_requires_upgrade'),
                ], 403);
            }

            return redirect()->back()->with('warning', __('eshop360::messages.feature_requires_upgrade'));
        }

        return $next($request);
    }
}
