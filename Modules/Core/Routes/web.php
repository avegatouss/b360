<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\AuditLogController;
use Modules\Core\Http\Controllers\BackupController;
use Modules\Core\Http\Controllers\CronLogController;
use Modules\Core\Http\Controllers\FileManagerController;
use Modules\Core\Http\Controllers\DocumentationController;
use Modules\Core\Http\Controllers\MaintenanceController;
use Modules\Core\Http\Controllers\ThemeController;
use Modules\Core\Http\Controllers\TourController;

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

/*
|--------------------------------------------------------------------------
| Maintenance Routes — Instance-scoped, admin only
|--------------------------------------------------------------------------
*/
Route::middleware([
    'web',
    'core.redirect.not_installed',
    'core.instance.bind',
    'core.instance.resolved',
    'core.spatie.team',
    'auth',
    'core.instance.member',
])->prefix('/i/{slug}')->group(function () {
    Route::post('/maintenance/toggle', [MaintenanceController::class, 'toggle'])
        ->name('maintenance.toggle');
    Route::put('/maintenance', [MaintenanceController::class, 'update'])
        ->name('maintenance.update');

    // Backups
    Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('/backups', [BackupController::class, 'create'])->name('backups.create');
    Route::get('/backups/{filename}/download', [BackupController::class, 'download'])->name('backups.download');
    Route::post('/backups/{filename}/restore', [BackupController::class, 'restore'])->name('backups.restore');
    Route::delete('/backups/{filename}', [BackupController::class, 'destroy'])->name('backups.destroy');

    // File Manager
    Route::get('/files', [FileManagerController::class, 'index'])->name('file-manager.index');
    Route::post('/files/upload', [FileManagerController::class, 'upload'])->name('file-manager.upload');
    Route::get('/files/download', [FileManagerController::class, 'download'])->name('file-manager.download');
    Route::delete('/files', [FileManagerController::class, 'destroy'])->name('file-manager.destroy');
    Route::post('/files/folder', [FileManagerController::class, 'createFolder'])->name('file-manager.create-folder');

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{id}', [AuditLogController::class, 'show'])->name('audit-logs.show');

    // Guided Tours API
    Route::prefix('tours')->name('tours.')->group(function () {
        Route::get('/available', [TourController::class, 'available'])->name('available');
        Route::get('/{tourId}/steps', [TourController::class, 'steps'])->name('steps');
        Route::post('/{tourId}/complete', [TourController::class, 'complete'])->name('complete');
        Route::post('/reset', [TourController::class, 'reset'])->name('reset');
    });

    // Documentation
    Route::prefix('documentation')->name('documentation.')->group(function () {
        Route::get('/', [DocumentationController::class, 'index'])->name('index');
        Route::get('/search', [DocumentationController::class, 'search'])->name('search');
        Route::get('/create', [DocumentationController::class, 'create'])->name('create');
        Route::post('/', [DocumentationController::class, 'store'])->name('store');
        Route::get('/{page}', [DocumentationController::class, 'show'])->name('show');
        Route::get('/{page}/edit', [DocumentationController::class, 'edit'])->name('edit');
        Route::put('/{page}', [DocumentationController::class, 'update'])->name('update');
        Route::delete('/{page}', [DocumentationController::class, 'destroy'])->name('destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes — Global (auth only, super-admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/theme/switch', [ThemeController::class, 'switch'])->name('theme.switch');

    // Cron monitoring
    Route::get('/admin/cron-logs', [CronLogController::class, 'index'])->name('admin.cron-logs');
});
