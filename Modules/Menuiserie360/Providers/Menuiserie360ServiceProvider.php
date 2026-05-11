<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider principal du module Menuiserie360 (L4).
 *
 * Décisions architecturales (cf. spec v1.3) :
 *   - BC-Finance autonome : aucun contrat Finance Eshop360 consommé.
 *   - Morphs Cas A : morph map propre dans boot() avec short keys
 *     (`mnu.*`), aucune entrée ajoutée au morph map central Eshop360.
 *   - Consommation Eshop360 limitée aux contrats `Modules/Eshop360/Contracts/*`
 *     (ADR-021) — bindings résolus côté Eshop360, rien à bind ici pour eux.
 *   - Bindings INTERNES : interfaces `StockContract` et `ClientRepositoryContract`
 *     liées à leurs implémentations Menuiserie360. Liaisons à compléter au fil
 *     des phases P1..P5 quand les implémentations existeront.
 */
final class Menuiserie360ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'menuiserie360');

        // Bindings internes : interfaces Menuiserie360 → implémentations.
        // P1-3 : StockMatiereService implémente StockContract.
        // P1-5 : ClientMenuiserieRepository implémente ClientRepositoryContract.
        $this->app->singleton(
            \Modules\Menuiserie360\Domain\Stock\Contracts\StockContract::class,
            \Modules\Menuiserie360\Domain\Stock\Services\StockMatiereService::class,
        );
        $this->app->singleton(
            \Modules\Menuiserie360\Domain\Client\Contracts\ClientRepositoryContract::class,
            \Modules\Menuiserie360\Domain\Client\Repositories\ClientMenuiserieRepository::class,
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'menuiserie360');

        $this->registerMorphMap();
    }

    /**
     * Morph map propre Menuiserie360 (Cas A — spec v1.3 §1.4ter).
     *
     * Short keys (`mnu.*`) plutôt que FQN — Menuiserie360 démarre sur tables
     * vides, on bénéficie d'emblée de clés courtes stables et indépendantes
     * du namespace PHP. Aucune entrée n'est ajoutée au morph map central
     * Eshop360 (Cas B explicitement écarté en spec v1.3).
     *
     * P2 — peuplement initial avec les modèles morphiques créés :
     * MenuiserieInvoice (payable_type sur mnu_payments).
     */
    private function registerMorphMap(): void
    {
        Relation::morphMap([
            // P2-7 : Finance autonome (BC-Finance v1.3 §4.5)
            'mnu.invoice' => \Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice::class,
            // mnu.payment intentionnellement absent — MenuiseriePayment n'est
            // jamais TARGET d'un morphTo (il est lui-même morphTo via payable),
            // donc pas besoin d'entrée dans le map. Si un autre modèle
            // morphTo vers MenuiseriePayment apparaît un jour, ajouter ici.
            //
            // Modèles non-morphiques (Devis, BonCommande, OF, Chantier) —
            // pas d'entrée morph map nécessaire (ils sont référencés via FK
            // standard, pas via polymorphisme).
        ]);
    }
}
