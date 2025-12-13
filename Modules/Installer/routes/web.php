<?php

use Illuminate\Support\Facades\Route;
use Modules\Installer\Http\Controllers\InstallerController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('installers', InstallerController::class)->names('installer');
});
