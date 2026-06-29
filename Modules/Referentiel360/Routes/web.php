<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Referentiel360\Http\Controllers\FinanceReportingController;

/*
|--------------------------------------------------------------------------
| Routes Referentiel360 (L2 socle — ADR-030 / ADR-031)
|--------------------------------------------------------------------------
|
| Surface HTTP minimale : reporting financier consolidé LECTURE SEULE.
| Scopée sous `/i/{slug}/referentiel/` via le groupe instance B360.
|
| Middleware stack héritée (alias définis dans CoreHttpServiceProvider) :
|   - core.instance.bind     : résout {slug} → Instance
|   - core.instance.resolved : EnsureInstanceResolved
|   - core.spatie.team       : SetSpatieTeamContextFromInstance
|   - auth                   : Laravel standard
|   - core.instance.member   : user ∈ instance
|
| Permission : `can:referentiel.finance.view` (déclarée par le HooksProvider).
*/

Route::middleware([
    'web',
    'core.instance.bind',
    'core.instance.resolved',
    'core.spatie.team',
    'auth',
    'core.instance.member',
])
    ->prefix('/i/{slug}/referentiel')
    ->name('referentiel.')
    ->group(function () {
        Route::middleware('can:referentiel.finance.view')->group(function () {
            Route::get('/finance', [FinanceReportingController::class, 'index'])
                ->name('finance.reporting');
        });
    });
