<?php

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\CheckoutController;
use Modules\Billing\Http\Controllers\GatewaySettingsController;
use Modules\Billing\Http\Controllers\InvoiceController;
use Modules\Billing\Http\Controllers\PlanController;
use Modules\Billing\Http\Controllers\SubscriptionController;
use Modules\Billing\Http\Controllers\UpgradeController;

Route::middleware([
    'web',
    'core.redirect.not_installed',
    'core.instance.bind',
    'core.instance.resolved',
    'core.spatie.team',
    'auth',
    'core.instance.member',
])->prefix('/i/{slug}')->group(function () {

    // Billing overview
    Route::get('/billing', [SubscriptionController::class, 'index'])->name('billing.index');

    // Plans CRUD (super-admin only)
    Route::middleware('core.root.superadmin')->group(function () {
        Route::get('/billing/plans', [PlanController::class, 'index'])->name('billing.plans.index');
        Route::get('/billing/plans/create', [PlanController::class, 'create'])->name('billing.plans.create');
        Route::post('/billing/plans', [PlanController::class, 'store'])->name('billing.plans.store');
        Route::get('/billing/plans/{plan}', [PlanController::class, 'show'])->name('billing.plans.show');
        Route::put('/billing/plans/{plan}', [PlanController::class, 'update'])->name('billing.plans.update');
        Route::delete('/billing/plans/{plan}', [PlanController::class, 'destroy'])->name('billing.plans.destroy');
    });

    // Subscription management
    Route::post('/billing/subscribe/{plan}', [SubscriptionController::class, 'subscribe'])->name('billing.subscribe');
    Route::put('/billing/subscription', [SubscriptionController::class, 'update'])->name('billing.subscription.update');
    Route::delete('/billing/subscription', [SubscriptionController::class, 'cancel'])->name('billing.subscription.cancel');

    // Invoices
    Route::get('/billing/invoices', [InvoiceController::class, 'index'])->name('billing.invoices.index');
    Route::get('/billing/invoices/{invoice}', [InvoiceController::class, 'show'])->name('billing.invoices.show');
    Route::post('/billing/invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('billing.invoices.pay');

    // Upgrade & Checkout
    Route::get('/billing/upgrade', [UpgradeController::class, 'show'])->name('billing.upgrade');
    Route::post('/billing/checkout', [CheckoutController::class, 'initiate'])->name('billing.checkout');

    // Gateway settings (super-admin only)
    Route::middleware('core.root.superadmin')->group(function () {
        Route::get('/billing/gateways', [GatewaySettingsController::class, 'index'])->name('billing.gateways.index');
        Route::get('/billing/gateways/{gateway}', [GatewaySettingsController::class, 'edit'])->name('billing.gateways.edit');
        Route::put('/billing/gateways/{gateway}', [GatewaySettingsController::class, 'update'])->name('billing.gateways.update');
        Route::post('/billing/gateways/{gateway}/toggle', [GatewaySettingsController::class, 'toggle'])->name('billing.gateways.toggle');
        Route::post('/billing/gateways/{gateway}/test', [GatewaySettingsController::class, 'test'])->name('billing.gateways.test');
    });
});
