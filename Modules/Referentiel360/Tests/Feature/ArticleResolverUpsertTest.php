<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Modules\Core\Support\CurrentInstance;
use Modules\Referentiel360\Contracts\Article\ArticleAttributesDto;
use Modules\Referentiel360\Contracts\Article\ArticleWriter;
use Modules\Referentiel360\Domain\Article\Events\ArticleUpserted;
use Modules\Referentiel360\Domain\Article\Models\Article;
use Modules\Referentiel360\Domain\Article\Models\ArticleLink;
use Modules\Referentiel360\Tests\TestCase;

/**
 * Nominal + absence de dédup cross-module (ADR-030 / Lot 2).
 */
final class ArticleResolverUpsertTest extends TestCase
{
    private int $instanceId;

    private ArticleWriter $writer;

    protected function setUp(): void
    {
        parent::setUp();
        $root = $this->makeRootInstance();
        $this->instanceId = (int) $root->id;
        CurrentInstance::set($root);
        $this->writer = app(ArticleWriter::class);
    }

    public function test_nominal_upsert_creates_golden_record_and_link(): void
    {
        Event::fake([ArticleUpserted::class]);

        $dto = $this->writer->upsertFromModule($this->instanceId, 'mnu.catalog_item', new ArticleAttributesDto(
            localId: 42,
            code: 'PORTE-ALU-01',
            label: 'Porte aluminium battante',
            articleType: 'produit',
            unit: 'u',
            salePrice: '125000.0000',
            taxRate: '0.1800',
            sourceModule: 'menuiserie',
        ));

        $this->assertSame('PORTE-ALU-01', $dto->code);
        $this->assertSame('Porte aluminium battante', $dto->label);
        $this->assertSame('produit', $dto->articleType);
        $this->assertSame('u', $dto->unit);
        $this->assertNotEmpty($dto->articleUid);

        $this->assertSame(1, Article::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());

        $link = ArticleLink::withoutInstanceScope()
            ->where('instance_id', $this->instanceId)
            ->where('linkable_type', 'mnu.catalog_item')
            ->where('linkable_id', 42)
            ->first();
        $this->assertNotNull($link);
        $this->assertSame($dto->id, (int) $link->getAttribute('article_id'));

        Event::assertDispatched(ArticleUpserted::class, fn (ArticleUpserted $e): bool => $e->created === true && $e->localId === 42 && $e->linkType === 'mnu.catalog_item');
    }

    public function test_same_local_object_reupsert_is_idempotent(): void
    {
        $a = $this->writer->upsertFromModule($this->instanceId, 'mnu.catalog_item', new ArticleAttributesDto(
            localId: 7,
            code: 'C-7',
            label: 'Châssis',
        ));

        // 2e passage du même objet local : match par lien → même golden, non destructif.
        $b = $this->writer->upsertFromModule($this->instanceId, 'mnu.catalog_item', new ArticleAttributesDto(
            localId: 7,
            code: 'C-7',
            label: 'Châssis modifié',
            description: 'Détail ajouté',
        ));

        $this->assertSame($a->id, $b->id);
        $this->assertSame('Châssis', $b->label, 'mergeInto non destructif : label existant conservé');
        $this->assertSame('Détail ajouté', $b->description, 'trou complété');
        $this->assertSame(1, Article::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
        $this->assertSame(1, ArticleLink::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
    }

    public function test_no_cross_module_dedup_same_code_two_link_types_two_goldens(): void
    {
        // Même `code` mais 2 linkType distincts (modules disjoints) ⇒ 2 golden distincts.
        $a = $this->writer->upsertFromModule($this->instanceId, 'mnu.matiere', new ArticleAttributesDto(
            localId: 1,
            code: 'ALU-6060',
            label: 'Profilé aluminium 6060',
            articleType: 'matiere',
        ));

        $b = $this->writer->upsertFromModule($this->instanceId, 'eshop.product', new ArticleAttributesDto(
            localId: 1,
            code: 'ALU-6060',
            label: 'Kit alu 6060 (e-shop)',
            articleType: 'produit',
        ));

        $this->assertNotSame($a->id, $b->id, 'Aucune dédup par code : 2 golden distincts');
        $this->assertSame(2, Article::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
        $this->assertSame(2, ArticleLink::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
    }
}
