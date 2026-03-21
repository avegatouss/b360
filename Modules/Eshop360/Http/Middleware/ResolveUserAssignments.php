<?php

namespace Modules\Eshop360\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Modules\Eshop360\Services\EshopSettingsService;
use Modules\Eshop360\Services\UserResourceScopeService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Initializes the UserResourceScopeService for the current user
 * and shares pricing visibility flag + currency to all views.
 */
final class ResolveUserAssignments
{
    public function __construct(private UserResourceScopeService $scopeService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->scopeService->init($request->user());

        View::share('canSeePricing', $this->scopeService->canSeePricing());

        // Share currency symbol from invoice settings
        $invoiceSettings = app(EshopSettingsService::class)->get('invoice');
        View::share('eshopCurrency', $invoiceSettings['currency_symbol'] ?? 'FCFA');

        return $next($request);
    }
}
