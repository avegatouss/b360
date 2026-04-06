<?php

namespace Modules\Eshop360\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Billing\Services\FeatureResolver;
use Modules\Core\Support\CurrentInstance;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fallback middleware for feature gating when the Billing module is not loaded.
 *
 * When Billing IS loaded, BillingServiceProvider registers 'eshop.feature' pointing
 * to Modules\Billing\Http\Middleware\EnsureFeature — this class is never used.
 *
 * When Billing is NOT loaded, all features are allowed (no pay-wall enforcement).
 */
final class EnsurePaidFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        // If FeatureResolver is available (Billing loaded), delegate to it
        if (app()->bound(FeatureResolver::class)) {
            $instance = CurrentInstance::get();
            if ($instance && !app(FeatureResolver::class)->can($instance->id, $feature)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'feature_unavailable',
                        'feature' => $feature,
                        'message' => __('eshop360::messages.feature_requires_upgrade'),
                    ], 403);
                }
                return redirect()->back()->with('warning', __('eshop360::messages.feature_requires_upgrade'));
            }
        }

        // No Billing module → all features allowed (no pay-wall)
        return $next($request);
    }
}
