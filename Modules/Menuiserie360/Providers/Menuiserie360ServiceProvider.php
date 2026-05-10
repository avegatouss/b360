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
        // À compléter en P1 quand StockMatiereService et ClientMenuiserieRepository
        // seront créés. Pour P0 (squelette), les interfaces existent sans implémentation.
        // Aucun bind par défaut à ce stade — toute consommation explicite échouera
        // intentionnellement tant que les services concrets ne sont pas livrés.
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
     * À compléter au fur et à mesure que les modèles morphiques apparaissent
     * (MenuiserieInvoice, MenuiseriePayment, etc. — cf. §4.5 spec).
     */
    private function registerMorphMap(): void
    {
        Relation::morphMap([
            // Aucune entrée pour P0 — placeholder. Les modèles morphiques
            // arrivent en P2..P3 (BC-Sales, BC-Finance).
            //
            // Exemples cibles (à activer dès création des modèles) :
            // 'mnu.invoice'  => \Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice::class,
            // 'mnu.payment'  => \Modules\Menuiserie360\Domain\Finance\Models\MenuiseriePayment::class,
            // 'mnu.devis'    => \Modules\Menuiserie360\Domain\Commercial\Models\Devis::class,
        ]);
    }
}
