<?php

use Illuminate\Support\Facades\Route;
use Modules\Demo\Http\Controllers\DemoController;

Route::middleware([
    'web',
    'core.redirect.not_installed',
    'core.instance.bind',
    'core.instance.resolved',
    'core.spatie.team',
    'auth',
    'core.instance.member',
    'core.root.superadmin',
])->prefix('/i/{slug}')->group(function () {
    Route::get('/demo', [DemoController::class, 'index'])->name('demo.index');
    Route::post('/demo/seed', [DemoController::class, 'seed'])->name('demo.seed');
    Route::post('/demo/reset', [DemoController::class, 'reset'])->name('demo.reset');
});
