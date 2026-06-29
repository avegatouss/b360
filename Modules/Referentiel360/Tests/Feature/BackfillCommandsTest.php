<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Referentiel360\Contracts\Article\ArticleAttributesDto;
use Modules\Referentiel360\Contracts\Finance\FinanceAttributesDto;
use Modules\Referentiel360\Contracts\Party\PartyAttributesDto;
use Modules\Referentiel360\Tests\Support\FakeArticleSource;
use Modules\Referentiel360\Tests\Support\FakeFinanceSource;
use Modules\Referentiel360\Tests\Support\FakePartySource;
use Modules\Referentiel360\Tests\TestCase;

/**
 * ADR-030 / ADR-031 — Tests d'INTÉGRATION des commandes artisan de backfill.
 *
 * Couvre le trou laissé par les tests de Service : le chemin
 * `app()->tagged('referentiel.*_source')` n'était exercé par aucun test, et un
 * `array_values(...)` sur le `RewindableGenerator` retourné par `tagged()`
 * provoquait un `TypeError` runtime (corrigé en `iterator_to_array(..., false)`).
 *
 * Le tag est posé DANS le test (pas dans un provider) pour que `tagged()`
 * renvoie le générateur réel — exactement le chemin qui plantait. Si on remet
 * `array_values(app()->tagged(...))` dans une commande, les cas « ≥1 source »
 * ci-dessous échouent (TypeError), prouvant que le trou est couvert.
 */
final class BackfillCommandsTest extends TestCase
{
    private int $instanceId;

    protected function setUp(): void
    {
        parent::setUp();
        $root = $this->makeRootInstance();
        $this->instanceId = (int) $root->id;
        CurrentInstance::set($root);
    }

    // ---------------------------------------------------------------------
    // Cas 1 — 0 source taggée ⇒ no-op sûr (pas d'exception).
    // ---------------------------------------------------------------------

    public function test_tiers_command_is_noop_without_source(): void
    {
        // Les modules L3 activés peuvent tagger des sources réelles au boot :
        // on garantit ici la précondition « 0 source » avant d'exécuter.
        $this->forgetTag('referentiel.party_source');

        $this->artisan('referentiel:backfill-tiers', ['--dry-run' => true])
            ->expectsOutputToContain('Aucune PartySource enregistrée')
            ->assertSuccessful();

        $this->assertSame(0, DB::table('ref_parties')->count());
    }

    public function test_articles_command_is_noop_without_source(): void
    {
        $this->forgetTag('referentiel.article_source');

        $this->artisan('referentiel:backfill-articles', ['--dry-run' => true])
            ->expectsOutputToContain('Aucune ArticleSource enregistrée')
            ->assertSuccessful();

        $this->assertSame(0, DB::table('ref_articles')->count());
    }

    public function test_finance_command_is_noop_without_source(): void
    {
        $this->forgetTag('referentiel.finance_source');

        $this->artisan('referentiel:backfill-finance', ['--dry-run' => true])
            ->expectsOutputToContain('Aucune FinanceSource enregistrée')
            ->assertSuccessful();

        $this->assertSame(0, DB::table('ref_documents_finance')->count());
    }

    // ---------------------------------------------------------------------
    // Cas 2 — ≥1 source taggée ⇒ exerce app()->tagged() (chemin qui plantait).
    // ---------------------------------------------------------------------

    public function test_tiers_command_runs_with_tagged_source(): void
    {
        $this->tagPartySource(new FakePartySource('mnu.client', [
            $this->instanceId => [
                new PartyAttributesDto(localId: 1, isCustomer: true, displayName: 'Alpha', email: 'a@x.ci'),
                new PartyAttributesDto(localId: 2, isCustomer: true, displayName: 'Beta', email: 'b@x.ci'),
            ],
        ]));

        $this->artisan('referentiel:backfill-tiers', ['--instance' => $this->instanceId])
            ->expectsOutputToContain('Backfill terminé.')
            ->assertSuccessful();

        $this->assertSame(2, DB::table('ref_parties')->where('instance_id', $this->instanceId)->count());
    }

    public function test_articles_command_runs_with_tagged_source(): void
    {
        $this->tagArticleSource(new FakeArticleSource('mnu.catalog_item', [
            $this->instanceId => [
                new ArticleAttributesDto(localId: 1, code: 'A-1', label: 'Alpha'),
                new ArticleAttributesDto(localId: 2, code: 'A-2', label: 'Beta'),
            ],
        ]));

        $this->artisan('referentiel:backfill-articles', ['--instance' => $this->instanceId])
            ->expectsOutputToContain('Backfill terminé.')
            ->assertSuccessful();

        $this->assertSame(2, DB::table('ref_articles')->where('instance_id', $this->instanceId)->count());
    }

    public function test_finance_command_runs_with_tagged_source(): void
    {
        $this->tagFinanceSource(new FakeFinanceSource('mnu.invoice', [
            $this->instanceId => [
                new FinanceAttributesDto(localId: 1, documentNumber: 'F-1', amountTtc: '120.00', paidAmount: '50.00', sourceModule: 'menuiserie'),
            ],
        ]));

        $this->artisan('referentiel:backfill-finance', ['--instance' => $this->instanceId])
            ->expectsOutputToContain('Backfill terminé.')
            ->assertSuccessful();

        $this->assertSame(1, DB::table('ref_documents_finance')->where('instance_id', $this->instanceId)->count());
    }

    // ---------------------------------------------------------------------
    // Cas 3 — --dry-run ⇒ 0 row écrite (la source est taggée, mais on simule).
    // ---------------------------------------------------------------------

    public function test_tiers_command_dry_run_writes_nothing(): void
    {
        $this->tagPartySource(new FakePartySource('mnu.client', [
            $this->instanceId => [
                new PartyAttributesDto(localId: 1, isCustomer: true, displayName: 'Alpha', email: 'a@x.ci'),
            ],
        ]));

        $this->artisan('referentiel:backfill-tiers', [
            '--instance' => $this->instanceId,
            '--dry-run' => true,
        ])->assertSuccessful();

        $this->assertSame(0, DB::table('ref_parties')->count());
        $this->assertSame(0, DB::table('ref_party_links')->count());
    }

    public function test_articles_command_dry_run_writes_nothing(): void
    {
        $this->tagArticleSource(new FakeArticleSource('mnu.catalog_item', [
            $this->instanceId => [
                new ArticleAttributesDto(localId: 1, code: 'A-1', label: 'Alpha'),
            ],
        ]));

        $this->artisan('referentiel:backfill-articles', [
            '--instance' => $this->instanceId,
            '--dry-run' => true,
        ])->assertSuccessful();

        $this->assertSame(0, DB::table('ref_articles')->count());
        $this->assertSame(0, DB::table('ref_article_links')->count());
    }

    public function test_finance_command_dry_run_writes_nothing(): void
    {
        $this->tagFinanceSource(new FakeFinanceSource('mnu.invoice', [
            $this->instanceId => [
                new FinanceAttributesDto(localId: 1, documentNumber: 'F-1', amountTtc: '120.00', paidAmount: '50.00', sourceModule: 'menuiserie'),
            ],
        ]));

        $this->artisan('referentiel:backfill-finance', [
            '--instance' => $this->instanceId,
            '--dry-run' => true,
        ])->assertSuccessful();

        $this->assertSame(0, DB::table('ref_documents_finance')->count());
        $this->assertSame(0, DB::table('ref_finance_links')->count());
    }

    // ---------------------------------------------------------------------
    // Cas 4 — run réel (sans --dry-run) ⇒ rows + liens créés.
    // ---------------------------------------------------------------------

    public function test_tiers_command_real_run_creates_rows_and_links(): void
    {
        $this->tagPartySource(new FakePartySource('mnu.client', [
            $this->instanceId => [
                new PartyAttributesDto(localId: 1, isCustomer: true, displayName: 'Alpha', email: 'a@x.ci'),
            ],
        ]));

        $this->artisan('referentiel:backfill-tiers', ['--instance' => $this->instanceId])
            ->assertSuccessful();

        $this->assertSame(1, DB::table('ref_parties')->where('instance_id', $this->instanceId)->count());
        $this->assertSame(1, DB::table('ref_party_links')->where('instance_id', $this->instanceId)->count());
    }

    public function test_finance_command_real_run_creates_rows_and_links(): void
    {
        $this->tagFinanceSource(new FakeFinanceSource('mnu.invoice', [
            $this->instanceId => [
                new FinanceAttributesDto(localId: 1, documentNumber: 'F-1', amountTtc: '120.00', paidAmount: '50.00', sourceModule: 'menuiserie'),
            ],
        ]));

        $this->artisan('referentiel:backfill-finance', ['--instance' => $this->instanceId])
            ->assertSuccessful();

        $this->assertSame(1, DB::table('ref_documents_finance')->where('instance_id', $this->instanceId)->count());
        $this->assertSame(1, DB::table('ref_finance_links')->where('instance_id', $this->instanceId)->count());
    }

    // ---------------------------------------------------------------------
    // Helpers — tag d'une instance déjà construite dans le container.
    //
    // On bind une instance concrète sous un abstract dédié puis on tague cet
    // abstract : `app()->tagged()` renvoie alors le RewindableGenerator réel
    // qui re-résout cet abstract — exactement le chemin de production.
    // ---------------------------------------------------------------------

    private function tagPartySource(FakePartySource $source): void
    {
        // On repart d'un set de tags vide pour ne dépendre d'aucune source L3
        // ambiante (les modules activés peuvent en tagger au boot).
        $this->forgetTag('referentiel.party_source');
        $this->app->instance(FakePartySource::class, $source);
        $this->app->tag([FakePartySource::class], 'referentiel.party_source');
    }

    private function tagArticleSource(FakeArticleSource $source): void
    {
        $this->forgetTag('referentiel.article_source');
        $this->app->instance(FakeArticleSource::class, $source);
        $this->app->tag([FakeArticleSource::class], 'referentiel.article_source');
    }

    private function tagFinanceSource(FakeFinanceSource $source): void
    {
        $this->forgetTag('referentiel.finance_source');
        $this->app->instance(FakeFinanceSource::class, $source);
        $this->app->tag([FakeFinanceSource::class], 'referentiel.finance_source');
    }

    /**
     * Vide le tag ciblé dans le container (propriété protégée `$tags`) afin de
     * neutraliser toute source L3 enregistrée au boot et garantir un état
     * déterministe. Le helper `tag()`/`tagged()` reste exercé normalement.
     */
    private function forgetTag(string $tag): void
    {
        $container = $this->app;
        $property = new \ReflectionProperty(\Illuminate\Container\Container::class, 'tags');
        $property->setAccessible(true);

        /** @var array<string, array<int, string>> $tags */
        $tags = $property->getValue($container);
        $tags[$tag] = [];
        $property->setValue($container, $tags);
    }
}
