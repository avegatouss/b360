<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Modules\ModuleManager;
use Modules\Menuiserie360\Domain\Client\Models\ClientMenuiserie;
use Modules\Menuiserie360\Domain\Finance\Jobs\RelancerFacturesImpayeesJob;
use Modules\Menuiserie360\Domain\Purchasing\Models\Fournisseur;
use Modules\Menuiserie360\Integration\Referentiel\ClientReferentielObserver;
use Modules\Menuiserie360\Integration\Referentiel\FournisseurReferentielObserver;
use Modules\Menuiserie360\Integration\Referentiel\MenuiserieClientPartySource;
use Modules\Menuiserie360\Integration\Referentiel\MenuiserieSupplierPartySource;

/**
 * Service provider principal du module Menuiserie360 (L3).
 *
 * Décisions architecturales (cf. spec v1.3) :
 *   - BC-Finance autonome : aucun contrat Finance Eshop360 consommé.
 *   - Morphs Cas A : morph map propre dans boot() avec short keys
 *     (`mnu.*`), aucune entrée ajoutée au morph map central Eshop360.
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
        $this->registerScheduledJobs();
        $this->registerReferentielIntegration();
    }

    /**
     * Lot 1.a (ADR-030) — Branchement conditionnel sur Referentiel360 (L2).
     *
     * UNIQUEMENT si Referentiel360 est activé : on tag les 2 PartySource pour le
     * backfill et on attache les 2 observers best-effort qui poussent les tiers
     * vers le golden record. Si Referentiel360 est éteint : aucun enregistrement
     * — Menuiserie360 reste totalement autonome (ADR-023).
     */
    private function registerReferentielIntegration(): void
    {
        if (! $this->app->make(ModuleManager::class)->isEnabled('REFERENTIEL360')) {
            return;
        }

        // Backfill : 2 sources taggées (consommées par referentiel:backfill-tiers).
        $this->app->tag(
            [MenuiserieClientPartySource::class, MenuiserieSupplierPartySource::class],
            'referentiel.party_source',
        );

        // Temps réel : observers best-effort (push via DB::afterCommit).
        ClientMenuiserie::observe($this->app->make(ClientReferentielObserver::class));
        Fournisseur::observe($this->app->make(FournisseurReferentielObserver::class));
    }

    /**
     * P3-7 — Programme la relance quotidienne des factures impayées.
     *
     * Exécution chaque jour à 08:00 ; idempotent grâce au cooldown 7j
     * du RelanceService → ré-exécutions silencieuses sur même invoice.
     */
    private function registerScheduledJobs(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->job(new RelancerFacturesImpayeesJob)
                ->dailyAt('08:00')
                ->name('menuiserie360.relancer.factures.impayees')
                ->withoutOverlapping();
        });
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
