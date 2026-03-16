<?php

use Illuminate\Support\Facades\Route;
use Modules\Lang\Http\Controllers\LangController;
use Modules\Lang\Http\Controllers\TranslationController;

Route::middleware('web')->group(function () {
    Route::get('/lang/{locale}', [LangController::class, 'switch'])->name('lang.switch');
});

// Translation management routes (instance-scoped)
Route::middleware([
    'web',
    'core.redirect.not_installed',
    'core.instance.bind',
    'core.instance.resolved',
    'core.spatie.team',
    'auth',
    'core.instance.member',
])->prefix('/i/{slug}')->group(function () {
    Route::prefix('translations')->name('translations.')->group(function () {
        Route::get('/', [TranslationController::class, 'index'])->name('index');
        Route::post('/', [TranslationController::class, 'store'])->name('store');
        Route::post('/bulk', [TranslationController::class, 'bulkUpdate'])->name('bulk-update');
        Route::delete('/{translation}', [TranslationController::class, 'destroy'])->name('destroy');
        Route::get('/export', [TranslationController::class, 'export'])->name('export');
        Route::post('/import', [TranslationController::class, 'import'])->name('import');
    });
});
