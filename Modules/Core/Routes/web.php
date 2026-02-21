<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Middleware\EnsureInstanceResolved;
use Modules\Core\Http\Middleware\EnsureInstanceMembershipActive;

Route::middleware([
    'web',
    // EnsureInstalled + InstanceMiddleware should already be globally registered
    EnsureInstanceResolved::class,
    'core.spatie.team',
    'auth',
    EnsureInstanceMembershipActive::class,
])->group(function () {
    // Phase 1: core foundation only (no UI here yet). Other modules own /login, /, etc.
    Route::get('/_core/health', fn() => response()->json(['ok' => true]))->name('core.health');
    Route::get('/', fn() => response('', 204))
        ->middleware(['core.redirect.not_installed', 'core.redirect.root_after_install'])
        ->name('root');
});
