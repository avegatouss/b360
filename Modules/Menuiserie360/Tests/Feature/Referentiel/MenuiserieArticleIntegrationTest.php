<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Feature\Referentiel;

use App\Instances\Instance;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Menuiserie360\Domain\Catalog\Enums\ItemType;
use Modules\Menuiserie360\Domain\Catalog\Models\CatalogItem;
use Modules\Menuiserie360\Domain\Stock\Models\MatierePremiere;
use Modules\Menuiserie360\Integration\Referentiel\CatalogItemReferentielObserver;
use Modules\Menuiserie360\Integration\Referentiel\MenuiserieArticleMapper;
use Modules\Menuiserie360\Integration\Referentiel\MenuiserieCatalogItemArticleSource;
use Modules\Menuiserie360\Tests\TestCase;
use Modules\Referentiel360\Contracts\Article\ArticleAttributesDto;
use Modules\Referentiel360\Contracts\Article\ArticleDto;
use Modules\Referentiel360\Contracts\Article\ArticleWriter;

/**
 * Lot 2.a (ADR-030) — Intégration articles Menuiserie360 → Referentiel360.
 *
 * Couvre : push observer post-commit (catalog_item + matière), articleType déduit
 * de l'item_type, salePrice null pour une matière, best-effort (ArticleWriter qui
 * throw ne casse pas la création) et ArticleSource backfill.
 *
 * Note RefreshDatabase + afterCommit : on installe le transaction manager de test
 * {@see ImmediateAfterCommitTransactionsManager} (cf. Lot 1.a) qui exécute les
 * callbacks afterCommit au commit de la transaction métier imbriquée (niveau 1).
 */
final class MenuiserieArticleIntegrationTest extends TestCase
{
    private Instance $instance;

    private int $instanceId;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $this->instance = $this->makeRootInstance();
        $this->instanceId = (int) $this->instance->id;
        CurrentInstance::set($this->instance);
        TeamContext::set(0);

        // Force l'exécution des afterCommit au commit vers le niveau 1 (cf. classe).
        $manager = new ImmediateAfterCommitTransactionsManager;
        foreach (['sqlite', 'system'] as $name) {
            DB::connection($name)->setTransactionManager($manager);
        }
        $this->app->instance('db.transactions', $manager);
    }

    public function test_creating_catalog_item_pushes_one_article_and_one_link(): void
    {
        $item = DB::transaction(fn (): CatalogItem => CatalogItem::create([
            'instance_id' => $this->instanceId,
            'code' => 'SRV-REF-001',
            'libelle' => 'Pose vitrage',
            'item_type' => ItemType::SERVICE->value,
            'unite' => 'h',
            'prix_unitaire_ht' => 15000,
            'taux_tva' => 0.18,
            'is_active' => true,
        ]));

        $this->assertSame(1, DB::table('ref_articles')->where('instance_id', $this->instanceId)->count());

        $article = DB::table('ref_articles')->where('instance_id', $this->instanceId)->first();
        $this->assertNotNull($article);
        // item_type=service ⇒ articleType 'service'.
        $this->assertSame('service', $article->article_type);

        $link = DB::table('ref_article_links')
            ->where('instance_id', $this->instanceId)
            ->where('linkable_type', 'mnu.catalog_item')
            ->where('linkable_id', $item->getKey())
            ->first();

        $this->assertNotNull($link);
    }

    public function test_creating_matiere_premiere_pushes_matiere_article_without_sale_price(): void
    {
        $matiere = DB::transaction(fn (): MatierePremiere => MatierePremiere::create([
            'instance_id' => $this->instanceId,
            'code' => 'MAT-REF-001',
            'designation' => 'Profilé alu 40x40',
            'categorie' => 'profile_alu',
            'unite' => 'm_lineaire',
            'prix_unitaire' => 3200,
            'seuil_alerte' => 10,
            'is_active' => true,
        ]));

        $article = DB::table('ref_articles')
            ->where('instance_id', $this->instanceId)
            ->first();

        $this->assertNotNull($article);
        $this->assertSame('matiere', $article->article_type);
        // prix_unitaire est un coût d'achat, pas une vente ⇒ sale_price null.
        $this->assertNull($article->sale_price);
        $this->assertNull($article->tax_rate);

        $link = DB::table('ref_article_links')
            ->where('instance_id', $this->instanceId)
            ->where('linkable_type', 'mnu.matiere')
            ->where('linkable_id', $matiere->getKey())
            ->first();

        $this->assertNotNull($link);
    }

    public function test_push_is_best_effort_when_article_writer_throws(): void
    {
        // ArticleWriter qui throw : la création de l'item doit réussir, l'exception avalée.
        $this->app->instance(ArticleWriter::class, new class implements ArticleWriter
        {
            public function upsertFromModule(int $instanceId, string $linkType, ArticleAttributesDto $attrs): ArticleDto
            {
                throw new \RuntimeException('Referentiel indisponible');
            }

            public function link(int $instanceId, int $articleId, string $linkType, int $localId): void
            {
                throw new \RuntimeException('Referentiel indisponible');
            }
        });

        // Réattacher l'observer avec le writer mocké (boot a câblé l'ancien).
        CatalogItem::observe($this->app->make(CatalogItemReferentielObserver::class));

        $item = DB::transaction(fn (): CatalogItem => CatalogItem::create([
            'instance_id' => $this->instanceId,
            'code' => 'PF-BE-001',
            'libelle' => 'Fenêtre survivante',
            'item_type' => ItemType::PRODUIT_FINI->value,
            'unite' => 'u',
            'prix_unitaire_ht' => 90000,
            'is_active' => true,
        ]));

        // L'item existe malgré l'échec du référentiel.
        $this->assertDatabaseHas('mnu_catalog_items', [
            'id' => $item->getKey(),
            'libelle' => 'Fenêtre survivante',
        ]);
        // Aucun article créé (writer a échoué silencieusement).
        $this->assertSame(0, DB::table('ref_articles')->where('instance_id', $this->instanceId)->count());
    }

    public function test_catalog_item_article_source_yields_expected_dto(): void
    {
        CatalogItem::create([
            'instance_id' => $this->instanceId,
            'code' => 'MAT-SRC-001',
            'libelle' => 'Tube alu',
            'item_type' => ItemType::MATIERE_PREMIERE->value,
            'category_code' => 'CAT-ALU',
            'unite' => 'ml',
            'prix_unitaire_ht' => 2500,
            'taux_tva' => 0.18,
            'is_active' => true,
        ]);

        $source = new MenuiserieCatalogItemArticleSource(new MenuiserieArticleMapper);

        $this->assertSame('mnu.catalog_item', $source->linkType());

        $dtos = iterator_to_array($source->each($this->instanceId));
        $this->assertCount(1, $dtos);

        /** @var ArticleAttributesDto $dto */
        $dto = $dtos[0];
        $this->assertSame('MAT-SRC-001', $dto->code);
        $this->assertSame('Tube alu', $dto->label);
        // item_type=matiere_premiere ⇒ articleType 'matiere'.
        $this->assertSame('matiere', $dto->articleType);
        $this->assertSame('ml', $dto->unit);
        $this->assertSame('2500.0000', $dto->salePrice);
        $this->assertSame('0.1800', $dto->taxRate);
        $this->assertSame('CAT-ALU', $dto->categoryLabel);
        $this->assertSame('menuiserie', $dto->sourceModule);
    }
}
