<?php

use Illuminate\Support\Facades\Route;
use Modules\Instances\Http\Controllers\InstanceController;

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

    Route::get('/instances', [InstanceController::class, 'index'])->name('instances.index');
    Route::get('/instances/create', [InstanceController::class, 'create'])->name('instances.create');
    Route::post('/instances', [InstanceController::class, 'store'])->name('instances.store');
    Route::get('/instances/{instance}', [InstanceController::class, 'show'])->name('instances.show');
    Route::get('/instances/{instance}/edit', [InstanceController::class, 'edit'])->name('instances.edit');
    Route::put('/instances/{instance}', [InstanceController::class, 'update'])->name('instances.update');
    Route::delete('/instances/{instance}', [InstanceController::class, 'destroy'])->name('instances.destroy');
    Route::put('/instances/{instance}/toggle', [InstanceController::class, 'toggle'])->name('instances.toggle');
});
