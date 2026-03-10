<?php

use Illuminate\Support\Facades\Route;
use Modules\Lang\Http\Controllers\LangController;

Route::middleware('web')->group(function () {
    Route::get('/lang/{locale}', [LangController::class, 'switch'])->name('lang.switch');
});
