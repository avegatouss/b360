<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Menuiserie360\Http\Controllers\Chantier\ChantierController;
use Modules\Menuiserie360\Http\Controllers\Client\ClientController;
use Modules\Menuiserie360\Http\Controllers\Commercial\DevisController;
use Modules\Menuiserie360\Http\Controllers\Finance\FactureMenuiserieController;
use Modules\Menuiserie360\Http\Controllers\Production\OrdreFabricationController;
use Modules\Menuiserie360\Http\Controllers\Reporting\DashboardMenuiserieController;
use Modules\Menuiserie360\Http\Controllers\Sales\BonCommandeController;
use Modules\Menuiserie360\Http\Controllers\Stock\StockMatiereController;

/*
|--------------------------------------------------------------------------
| Routes Menuiserie360 (L4)
|--------------------------------------------------------------------------
|
| Toutes les routes du module sont scopées sous `/i/{slug}/menuiserie/`
| via le groupe instance B360 + le préfixe `menuiserie/` propre au module.
|
| Middleware stack héritée (cf. spec v1.3 §5.1) :
|   - `auth`
|   - `instance` (BindInstanceFromRoute)
|   - `instance.membership` (EnsureInstanceMembershipActive)
|   - `spatie.team` (SetSpatieTeamContextFromInstance)
|
| Permissions : check via `can:menuiserie.<bc>.<action>` Spatie.
| Feature gating Billing : non activé en MVP (à ajouter dans un lot
| dédié quand l'humain configurera les Plans Billing).
*/

Route::middleware([
    'auth',
    'instance',
    'instance.membership',
    'spatie.team',
])
    ->prefix('menuiserie')
    ->name('menuiserie.')
    ->group(function () {
        // ─── BC-Commercial : Devis ───────────────────────────────
        Route::prefix('devis')->name('devis.')->middleware('can:menuiserie.devis.view')->group(function () {
            Route::get('/', [DevisController::class, 'index'])->name('index');
            Route::get('/{devis}', [DevisController::class, 'show'])->name('show');
            Route::get('/{devis}/pdf', [DevisController::class, 'pdf'])->name('pdf');
        });
        Route::middleware('can:menuiserie.devis.create')->group(function () {
            Route::get('/devis/create', [DevisController::class, 'create'])->name('devis.create');
            Route::post('/devis', [DevisController::class, 'store'])->name('devis.store');
        });
        Route::post('/devis/{devis}/accepter', [DevisController::class, 'accepter'])
            ->name('devis.accepter')
            ->middleware('can:menuiserie.bc.validate');

        // ─── BC-Clients ──────────────────────────────────────────
        Route::prefix('clients')->name('clients.')->middleware('can:menuiserie.client.view')->group(function () {
            Route::get('/', [ClientController::class, 'index'])->name('index');
            Route::get('/{customerId}', [ClientController::class, 'show'])->name('show');
        });

        // ─── BC-Sales : BonCommande ──────────────────────────────
        Route::prefix('bons-commande')->name('bc.')->middleware('can:menuiserie.devis.view')->group(function () {
            Route::get('/', [BonCommandeController::class, 'index'])->name('index');
            Route::get('/{bc}', [BonCommandeController::class, 'show'])->name('show');
        });

        // ─── BC-Production : Ordres de fabrication ───────────────
        Route::prefix('production')->name('production.')->middleware('can:menuiserie.of.create')->group(function () {
            Route::get('/', [OrdreFabricationController::class, 'index'])->name('index');
            Route::get('/{of}', [OrdreFabricationController::class, 'show'])->name('show');
            Route::post('/{of}/lancer', [OrdreFabricationController::class, 'lancer'])->name('lancer');
            Route::post('/{of}/terminer', [OrdreFabricationController::class, 'terminer'])->name('terminer');
        });

        // ─── BC-Chantier ─────────────────────────────────────────
        Route::prefix('chantiers')->name('chantiers.')->middleware('can:menuiserie.chantier.view')->group(function () {
            Route::get('/', [ChantierController::class, 'index'])->name('index');
            Route::get('/{chantier}', [ChantierController::class, 'show'])->name('show');
        });
        Route::middleware('can:menuiserie.chantier.update')->group(function () {
            Route::post('/chantiers/{chantier}/avancer', [ChantierController::class, 'avancer'])->name('chantiers.avancer');
        });

        // ─── BC-Stock : Matières premières ───────────────────────
        Route::prefix('stocks')->name('stocks.')->middleware('can:menuiserie.stock.adjust')->group(function () {
            Route::get('/', [StockMatiereController::class, 'index'])->name('index');
            Route::get('/{matiere}', [StockMatiereController::class, 'show'])->name('show');
            Route::post('/{matiere}/recevoir', [StockMatiereController::class, 'recevoir'])->name('recevoir');
        });

        // ─── BC-Finance : Factures ───────────────────────────────
        Route::prefix('factures')->name('factures.')->middleware('can:menuiserie.invoice.create')->group(function () {
            Route::get('/', [FactureMenuiserieController::class, 'index'])->name('index');
            Route::get('/{invoice}', [FactureMenuiserieController::class, 'show'])->name('show');
        });

        // ─── BC-Reporting : Dashboard ────────────────────────────
        Route::prefix('reporting')->name('reporting.')->middleware('can:menuiserie.report.view')->group(function () {
            Route::get('/', [DashboardMenuiserieController::class, 'index'])->name('index');
        });
    });
