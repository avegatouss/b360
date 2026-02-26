<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Core Routes
|--------------------------------------------------------------------------
|
| Health check (public) et route root (redirect).
|
*/

// Health check — pas de middleware instance nécessaire
Route::middleware(['web'])->group(function () {
    Route::get('/_core/health', fn () => response()->json(['ok' => true]))->name('core.health');
});

// Root route — redirige vers /login ou /install selon état
Route::middleware([
    'web',
    'core.redirect.not_installed',
    'core.redirect.root_after_install',
])->group(function () {
    Route::get('/', fn () => redirect('/login'))->name('root');
});
