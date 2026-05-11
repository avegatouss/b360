<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Menuiserie360\Http\Controllers\Alertes\AlerteController;
use Modules\Menuiserie360\Http\Controllers\Chantier\ChantierController;
use Modules\Menuiserie360\Http\Controllers\Client\ClientController;
use Modules\Menuiserie360\Http\Controllers\Commercial\DevisController;
use Modules\Menuiserie360\Http\Controllers\Commercial\TypeProduitController;
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
| Middleware stack héritée (alias définis dans CoreHttpServiceProvider) :
|   - `core.instance.bind` (BindInstanceFromRoute : résout {slug} → Instance)
|   - `core.instance.resolved` (EnsureInstanceResolved)
|   - `core.spatie.team` (SetSpatieTeamContextFromInstance)
|   - `auth` (Laravel standard)
|   - `core.instance.member` (EnsureInstanceMembershipActive : user ∈ instance)
|
| Permissions : check via `can:menuiserie.<bc>.<action>` Spatie.
| Feature gating Billing : non activé en MVP (à ajouter dans un lot
| dédié quand l'humain configurera les Plans Billing).
*/

Route::middleware([
    'web',
    'core.instance.bind',
    'core.instance.resolved',
    'core.spatie.team',
    'auth',
    'core.instance.member',
])
    ->prefix('/i/{slug}/menuiserie')
    ->name('menuiserie.')
    ->group(function () {
        // ─── BC-Commercial : Devis ───────────────────────────────
        // Les routes statiques (/create) doivent être déclarées AVANT les
        // routes paramétrées (/{devis}) pour éviter que Laravel ne route
        // /devis/create vers show() avec $devis = 'create' → 404.
        Route::prefix('devis')->name('devis.')->group(function () {
            // Création (permission .create) — déclarée en premier
            Route::middleware('can:menuiserie.devis.create')->group(function () {
                Route::get('/create', [DevisController::class, 'create'])->name('create');
                Route::post('/', [DevisController::class, 'store'])->name('store');
            });

            // Validation BC (permission .bc.validate)
            Route::post('/{devis}/accepter', [DevisController::class, 'accepter'])
                ->middleware('can:menuiserie.bc.validate')
                ->whereNumber('devis')
                ->name('accepter');

            // Lecture (permission .view) — {devis} contraint à numérique
            Route::middleware('can:menuiserie.devis.view')->group(function () {
                Route::get('/', [DevisController::class, 'index'])->name('index');
                Route::get('/{devis}', [DevisController::class, 'show'])->whereNumber('devis')->name('show');
                Route::get('/{devis}/pdf', [DevisController::class, 'pdf'])->whereNumber('devis')->name('pdf');
            });
        });

        // ─── BC-Clients ──────────────────────────────────────────
        Route::prefix('clients')->name('clients.')->middleware('can:menuiserie.client.view')->group(function () {
            Route::get('/', [ClientController::class, 'index'])->name('index');
            // Endpoint JSON pour autocomplete (M-UI-4) — déclaré avant /{customerId}
            // (de toute façon protégé par whereNumber sur la route show).
            Route::get('/search', [ClientController::class, 'search'])->name('search');
            Route::get('/{customerId}', [ClientController::class, 'show'])->whereNumber('customerId')->name('show');
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
            Route::post('/chantiers/{chantier}/terminer', [ChantierController::class, 'terminer'])->name('chantiers.terminer');
            Route::post('/chantiers/{chantier}/photos', [ChantierController::class, 'uploadPhoto'])->name('chantiers.photos.upload');
            Route::delete('/chantiers/{chantier}/photos/{media}', [ChantierController::class, 'deletePhoto'])->name('chantiers.photos.delete');
        });

        // ─── BC-Stock : Matières premières ───────────────────────
        // Routes statiques (/create) déclarées AVANT les paramétrées (/{matiere})
        // et {matiere} contraint à numérique pour éviter tout shadowing futur.
        Route::prefix('stocks')->name('stocks.')->group(function () {
            // CRUD matière (M-UI-2) — permission .matiere.manage
            Route::middleware('can:menuiserie.stock.matiere.manage')->group(function () {
                Route::get('/create', [StockMatiereController::class, 'create'])->name('create');
                Route::post('/', [StockMatiereController::class, 'store'])->name('store');
                Route::get('/{matiere}/edit', [StockMatiereController::class, 'edit'])->whereNumber('matiere')->name('edit');
                Route::put('/{matiere}', [StockMatiereController::class, 'update'])->whereNumber('matiere')->name('update');
                Route::delete('/{matiere}', [StockMatiereController::class, 'destroy'])->whereNumber('matiere')->name('destroy');
            });

            // Lecture + réception (permission .stock.adjust existante)
            Route::middleware('can:menuiserie.stock.adjust')->group(function () {
                Route::get('/', [StockMatiereController::class, 'index'])->name('index');
                Route::get('/{matiere}', [StockMatiereController::class, 'show'])->whereNumber('matiere')->name('show');
                Route::post('/{matiere}/recevoir', [StockMatiereController::class, 'recevoir'])->whereNumber('matiere')->name('recevoir');
            });
        });

        // ─── BC-Finance : Factures ───────────────────────────────
        Route::prefix('factures')->name('factures.')->middleware('can:menuiserie.invoice.create')->group(function () {
            Route::get('/', [FactureMenuiserieController::class, 'index'])->name('index');
            Route::get('/{invoice}', [FactureMenuiserieController::class, 'show'])->name('show');
            Route::post('/{invoice}/payments', [FactureMenuiserieController::class, 'recordPayment'])->name('payments.store');
        });

        // ─── BC-Reporting : Dashboard ────────────────────────────
        Route::prefix('reporting')->name('reporting.')->middleware('can:menuiserie.report.view')->group(function () {
            Route::get('/', [DashboardMenuiserieController::class, 'index'])->name('index');
            Route::get('/operations', [DashboardMenuiserieController::class, 'operations'])->name('operations');
            Route::get('/journal', [DashboardMenuiserieController::class, 'journal'])->name('journal');
            Route::get('/exports/comptable.csv', [DashboardMenuiserieController::class, 'exportComptable'])->name('exports.comptable');
        });

        // ─── M-UI-8 : Alertes opérationnelles ───────────────────
        Route::get('/alertes', [AlerteController::class, 'index'])
            ->middleware('can:menuiserie.report.view')
            ->name('alertes.index');

        // ─── BC-Commercial : Bibliothèque types produits (P2-C) ──
        Route::prefix('types-produits')->name('types-produits.')->middleware('can:menuiserie.devis.view')->group(function () {
            Route::get('/', [TypeProduitController::class, 'index'])->name('index');
            Route::get('/create', [TypeProduitController::class, 'create'])->name('create');
            Route::post('/', [TypeProduitController::class, 'store'])->name('store');
            Route::get('/{type}', [TypeProduitController::class, 'show'])->name('show');
            Route::get('/{type}/edit', [TypeProduitController::class, 'edit'])->name('edit');
            Route::put('/{type}', [TypeProduitController::class, 'update'])->name('update');
            Route::delete('/{type}', [TypeProduitController::class, 'destroy'])->name('destroy');
        });
    });
