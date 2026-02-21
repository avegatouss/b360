<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\DashboardController;

Route::middleware([
    'web',
    'core.redirect.not_installed',
    'core.instance.resolved',
    'core.spatie.team',
    'auth',
    'core.instance.member',
])->group(function () {
  /*  Route::get('/i/{slug}', DashboardController::class)
        ->middleware('can:dashboard.view')
        ->name('dashboard.instance');*/
});
