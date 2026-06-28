<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Modules\Referentiel360\Adapters\Eloquent\EloquentArticleReader;
use Modules\Referentiel360\Adapters\Eloquent\EloquentArticleResolver;
use Modules\Referentiel360\Adapters\Eloquent\EloquentArticleWriter;
use Modules\Referentiel360\Adapters\Eloquent\EloquentFinanceReader;
use Modules\Referentiel360\Adapters\Eloquent\EloquentFinanceWriter;
use Modules\Referentiel360\Adapters\Eloquent\EloquentPartyReader;
use Modules\Referentiel360\Adapters\Eloquent\EloquentPartyResolver;
use Modules\Referentiel360\Adapters\Eloquent\EloquentPartyWriter;
use Modules\Referentiel360\Console\Commands\BackfillArticlesCommand;
use Modules\Referentiel360\Console\Commands\BackfillFinanceCommand;
use Modules\Referentiel360\Console\Commands\BackfillTiersCommand;
use Modules\Referentiel360\Contracts\Article\ArticleReader;
use Modules\Referentiel360\Contracts\Article\ArticleResolver;
use Modules\Referentiel360\Contracts\Article\ArticleWriter;
use Modules\Referentiel360\Contracts\Finance\FinanceReader;
use Modules\Referentiel360\Contracts\Finance\FinanceWriter;
use Modules\Referentiel360\Contracts\Party\PartyReader;
use Modules\Referentiel360\Contracts\Party\PartyResolver;
use Modules\Referentiel360\Contracts\Party\PartyWriter;
use Modules\Referentiel360\Domain\Article\Models\Article;
use Modules\Referentiel360\Domain\Finance\Models\FinanceDocument;
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

        // Lot 2 — domaine Article (mêmes bindings ADR-021).
        $this->app->singleton(ArticleReader::class, EloquentArticleReader::class);
        $this->app->singleton(ArticleResolver::class, EloquentArticleResolver::class);
        $this->app->singleton(ArticleWriter::class, EloquentArticleWriter::class);

        // Lot 3 — domaine Finance (registre miroir, PAS de Resolver : pas de fallback).
        $this->app->singleton(FinanceReader::class, EloquentFinanceReader::class);
        $this->app->singleton(FinanceWriter::class, EloquentFinanceWriter::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->registerMorphMap();

        if ($this->app->runningInConsole()) {
            $this->commands([
                BackfillTiersCommand::class,
                BackfillArticlesCommand::class,
                BackfillFinanceCommand::class,
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
            'ref.article' => Article::class,
            'ref.finance_document' => FinanceDocument::class,
            // Short-keys des objets locaux (résolus en L3, pas de classe ici) :
            //   tiers   : mnu.client | mnu.supplier | eshop.customer | eshop.supplier
            //   article : mnu.catalog_item | mnu.matiere | eshop.product
            // déclarés en config 'referentiel360.link_types' / 'article_link_types'.
        ]);
    }
}
