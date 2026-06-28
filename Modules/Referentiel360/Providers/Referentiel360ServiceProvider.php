<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Modules\Referentiel360\Adapters\Eloquent\EloquentPartyReader;
use Modules\Referentiel360\Adapters\Eloquent\EloquentPartyResolver;
use Modules\Referentiel360\Adapters\Eloquent\EloquentPartyWriter;
use Modules\Referentiel360\Console\Commands\BackfillTiersCommand;
use Modules\Referentiel360\Contracts\Party\PartyReader;
use Modules\Referentiel360\Contracts\Party\PartyResolver;
use Modules\Referentiel360\Contracts\Party\PartyWriter;
use Modules\Referentiel360\Domain\Party\Models\Party;

/**
 * Service provider principal du module socle L2 `Referentiel360` (ADR-030).
 *
 * Bindings ADR-021 (interface → adapter Eloquent). Le module étant activé par
 * défaut, `PartyResolver` est bind sur EloquentPartyResolver (golden record).
 * Le NullPartyResolver (fallback module éteint) sera câblé par les Lots 1.a/1.b
 * selon l'état d'activation côté consommateur.
 */
final class Referentiel360ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'referentiel360');

        // ADR-021 : interfaces (Contracts) → adapters Eloquent.
        $this->app->singleton(PartyReader::class, EloquentPartyReader::class);
        $this->app->singleton(PartyResolver::class, EloquentPartyResolver::class);
        $this->app->singleton(PartyWriter::class, EloquentPartyWriter::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->registerMorphMap();

        if ($this->app->runningInConsole()) {
            $this->commands([
                BackfillTiersCommand::class,
            ]);
        }
    }

    /**
     * Morph map dédiée Referentiel360 — short-keys cohérentes avec l'existant
     * (`mnu.invoice`). `ref_party_links.linkable_type` stocke ces clés en string
     * libre (pas un morphTo classique) ; on déclare néanmoins la map pour la
     * cohérence projet et la stabilité des clés.
     */
    private function registerMorphMap(): void
    {
        Relation::morphMap([
            'ref.party' => Party::class,
            // Short-keys des objets locaux (résolus en L3, pas de classe ici) :
            //   mnu.client | mnu.supplier | eshop.customer | eshop.supplier
            // déclarés en config 'referentiel360.link_types'.
        ]);
    }
}
