<?php

use Illuminate\Support\Facades\Route;
use Modules\ModuleManager\Http\Controllers\ModuleController;

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

    Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index');
    Route::post('/modules/upload', [ModuleController::class, 'upload'])->name('modules.upload');
    Route::get('/modules/{name}', [ModuleController::class, 'show'])->name('modules.show');
    Route::put('/modules/{name}/toggle', [ModuleController::class, 'toggle'])->name('modules.toggle');
    Route::delete('/modules/{name}', [ModuleController::class, 'destroy'])->name('modules.destroy');
});
