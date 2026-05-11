<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Modules\Eshop360\Contracts\Catalog\CatalogReader;
use Modules\Eshop360\Contracts\Customer\CustomerReader;
use Modules\Eshop360\Contracts\Pricing\PricingResolver;
use Modules\Menuiserie360\Domain\Finance\Jobs\RelancerFacturesImpayeesJob;

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

        $this->preflightCheckEshop360Contracts();

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

    /**
     * R-403 — Menuiserie360 dépend fortement d'Eshop360 via les contracts
     * ADR-021 (CustomerReader/CatalogReader/PricingResolver).
     *
     * Quand Eshop360 est désactivé, ces contracts n'ont aucun binding et
     * la résolution silencieuse de `ClientMenuiserieRepository` (ou tout
     * service qui en dépend) lèvera `BindingResolutionException` au moment
     * où l'utilisateur ouvre un écran client/devis — symptôme opaque.
     *
     * Ce check transforme cet échec silencieux en signal explicite dans les
     * logs, sans bloquer le boot : Menuiserie360 conserve ses fonctionnalités
     * autonomes (Stock matières, Production OF) tant qu'aucun service ne
     * touche aux contracts Eshop360. Voir docs/memory/OPEN_RISKS.md R-403.
     */
    private function preflightCheckEshop360Contracts(): void
    {
        $missing = self::missingEshop360Contracts($this->app);

        if ($missing === []) {
            return;
        }

        Log::warning(self::buildPreflightWarningMessage($missing), ['missing_contracts' => $missing]);
    }

    /**
     * Liste les contracts Eshop360 attendus mais non bindés dans le container.
     * Exposé publiquement pour permettre le test unitaire sans dupliquer la liste.
     *
     * @return list<class-string>
     */
    public static function missingEshop360Contracts(\Illuminate\Contracts\Container\Container $container): array
    {
        return array_values(array_filter(
            self::eshop360RequiredContracts(),
            static fn (string $contract): bool => ! $container->bound($contract),
        ));
    }

    /**
     * @return list<class-string>
     */
    public static function eshop360RequiredContracts(): array
    {
        return [
            CustomerReader::class,
            CatalogReader::class,
            PricingResolver::class,
        ];
    }

    /**
     * @param  list<class-string>  $missing
     */
    public static function buildPreflightWarningMessage(array $missing): string
    {
        return 'Menuiserie360 actif mais Eshop360 désactivé — contracts ADR-021 absents : '
            .implode(', ', $missing)
            .'. Les fonctionnalités clients/catalogue/pricing lèveront BindingResolutionException '
            .'à l\'usage. Réactiver Eshop360 ou désactiver Menuiserie360 (voir OPEN_RISKS R-403).';
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'menuiserie360');

        $this->registerMorphMap();
        $this->registerScheduledJobs();
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
