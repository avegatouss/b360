<?php

declare(strict_types=1);

namespace Modules\Eshop360\Tests\Feature\Referentiel;

use App\Instances\Instance;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Integration\Referentiel\EshopArticleMapper;
use Modules\Eshop360\Integration\Referentiel\EshopProductArticleSource;
use Modules\Eshop360\Integration\Referentiel\ProductReferentielObserver;
use Modules\Eshop360\Tests\TestCase;
use Modules\Referentiel360\Contracts\Article\ArticleAttributesDto;
use Modules\Referentiel360\Contracts\Article\ArticleDto;
use Modules\Referentiel360\Contracts\Article\ArticleWriter;

/**
 * Lot 2.b (ADR-030) — Intégration Eshop360 (products) → Referentiel360 (articles).
 *
 * Couvre : push observer post-commit (création article + lien eshop.product),
 * code = sku ?? 'PRD-'.id, best-effort (ArticleWriter qui throw ne casse pas la
 * création), ArticleSource backfill.
 *
 * Même mécanique RefreshDatabase + afterCommit que le Lot 1.b : on installe un
 * transaction manager de test ({@see ImmediateAfterCommitTransactionsManager}).
 */
final class EshopArticleIntegrationTest extends TestCase
{
    private Instance $instance;

    private int $instanceId;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        // Hub admin authentifié : ChannelScope ne fail-close pas, channel_id
        // auto-injecté null (création autorisée pour un hub admin sans canal).
        [$this->instance] = $this->setUpInstanceWithAdmin();
        $this->instanceId = (int) $this->instance->id;
        CurrentInstance::set($this->instance);

        // Force l'exécution des afterCommit au commit vers le niveau 1 (cf. classe).
        $manager = new ImmediateAfterCommitTransactionsManager;
        foreach (['sqlite', 'system'] as $name) {
            DB::connection($name)->setTransactionManager($manager);
        }
        $this->app->instance('db.transactions', $manager);
    }

    public function test_creating_product_with_sku_pushes_one_article_and_link_with_code_sku(): void
    {
        $product = DB::transaction(fn (): Product => Product::create([
            'instance_id' => $this->instanceId,
            'name' => 'Tube PVC',
            'slug' => 'tube-pvc',
            'sku' => 'SKU-001',
            'price' => 1500,
            'tax_rate' => 18,
            'unit' => 'ml',
            'is_active' => true,
        ]));

        $this->assertSame(1, DB::table('ref_articles')->where('instance_id', $this->instanceId)->count());

        $link = DB::table('ref_article_links')
            ->where('instance_id', $this->instanceId)
            ->where('linkable_type', 'eshop.product')
            ->where('linkable_id', $product->getKey())
            ->first();

        $this->assertNotNull($link);

        $article = DB::table('ref_articles')->where('instance_id', $this->instanceId)->first();
        $this->assertNotNull($article);
        $this->assertSame('SKU-001', $article->code);
    }

    public function test_creating_product_without_sku_uses_prd_id_code(): void
    {
        $product = DB::transaction(fn (): Product => Product::create([
            'instance_id' => $this->instanceId,
            'name' => 'Sans SKU',
            'slug' => 'sans-sku',
            'sku' => null,
            'price' => 0,
            'is_active' => true,
        ]));

        $article = DB::table('ref_articles')->where('instance_id', $this->instanceId)->first();
        $this->assertNotNull($article);
        $this->assertSame('PRD-'.$product->getKey(), $article->code);
    }

    public function test_push_is_best_effort_when_article_writer_throws(): void
    {
        // ArticleWriter qui throw : la création product doit réussir, l'exception avalée.
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
        Product::observe($this->app->make(ProductReferentielObserver::class));

        $product = DB::transaction(fn (): Product => Product::create([
            'instance_id' => $this->instanceId,
            'name' => 'Survivor',
            'slug' => 'survivor',
            'sku' => 'SURV-001',
            'is_active' => true,
        ]));

        // Le product existe malgré l'échec du référentiel.
        $this->assertDatabaseHas('eshop_products', [
            'id' => $product->getKey(),
            'name' => 'Survivor',
        ]);
        // Aucun article créé (writer a échoué silencieusement).
        $this->assertSame(0, DB::table('ref_articles')->where('instance_id', $this->instanceId)->count());
    }

    public function test_product_article_source_yields_expected_dto(): void
    {
        $product = Product::create([
            'instance_id' => $this->instanceId,
            'name' => 'Source Produit',
            'slug' => 'source-produit',
            'sku' => 'SRC-001',
            'price' => 2000,
            'tax_rate' => 18,
            'unit' => 'pc',
            'description' => 'desc',
            'is_active' => true,
        ]);

        $source = new EshopProductArticleSource(new EshopArticleMapper);

        $this->assertSame('eshop.product', $source->linkType());

        $dtos = iterator_to_array($source->each($this->instanceId));
        $this->assertCount(1, $dtos);

        /** @var ArticleAttributesDto $dto */
        $dto = $dtos[0];
        $this->assertSame((int) $product->getKey(), $dto->localId);
        $this->assertSame('SRC-001', $dto->code);
        $this->assertSame('Source Produit', $dto->label);
        $this->assertSame('produit', $dto->articleType);
        $this->assertSame('pc', $dto->unit);
        $this->assertSame('desc', $dto->description);
        $this->assertTrue($dto->isActive);
        $this->assertSame('eshop', $dto->sourceModule);
        $this->assertNull($dto->categoryLabel);
    }
}
