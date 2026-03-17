<?php

use Illuminate\Support\Facades\Route;
use Modules\Currency\Http\Controllers\CurrencyController;

Route::middleware([
    'web',
    'core.redirect.not_installed',
    'core.instance.bind',
    'core.instance.resolved',
    'core.spatie.team',
    'auth',
    'core.instance.member',
])->prefix('/i/{slug}')->group(function () {
    Route::get('/currencies', [CurrencyController::class, 'index'])->name('currencies.index');
    Route::post('/currencies', [CurrencyController::class, 'store'])->name('currencies.store');
    Route::put('/currencies/{id}', [CurrencyController::class, 'update'])->name('currencies.update');
    Route::delete('/currencies/{id}', [CurrencyController::class, 'destroy'])->name('currencies.destroy');
    Route::post('/currencies/{id}/default', [CurrencyController::class, 'setDefault'])->name('currencies.set-default');
    Route::post('/currencies/update-rates', [CurrencyController::class, 'updateRates'])->name('currencies.update-rates');
});
