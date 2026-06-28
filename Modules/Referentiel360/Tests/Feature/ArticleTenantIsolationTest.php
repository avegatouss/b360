<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Tests\Feature;

use App\Instances\Instance;
use Modules\Core\Support\CurrentInstance;
use Modules\Referentiel360\Contracts\Article\ArticleAttributesDto;
use Modules\Referentiel360\Contracts\Article\ArticleWriter;
use Modules\Referentiel360\Domain\Article\Models\Article;
use Modules\Referentiel360\Tests\TestCase;

/**
 * ADR-030 / Lot 2 — Isolation multi-tenant : même code + même linkType + même
 * localId dans 2 instances ⇒ 2 articles distincts (aucune fusion cross-instance).
 */
final class ArticleTenantIsolationTest extends TestCase
{
    public function test_same_code_in_two_instances_yields_two_distinct_articles(): void
    {
        $a = $this->makeRootInstance();
        $b = Instance::create([
            'name' => 'Tenant B',
            'slug' => 'tenant-b-'.uniqid(),
            'is_active' => true,
            'meta' => [],
        ]);

        $writer = app(ArticleWriter::class);

        CurrentInstance::set($a);
        $pa = $writer->upsertFromModule((int) $a->id, 'mnu.catalog_item', new ArticleAttributesDto(
            localId: 1, code: 'SHARED', label: 'A',
        ));

        CurrentInstance::set($b);
        $pb = $writer->upsertFromModule((int) $b->id, 'mnu.catalog_item', new ArticleAttributesDto(
            localId: 1, code: 'SHARED', label: 'B',
        ));

        $this->assertNotSame($pa->id, $pb->id);
        $this->assertSame(1, Article::withoutInstanceScope()->where('instance_id', $a->id)->count());
        $this->assertSame(1, Article::withoutInstanceScope()->where('instance_id', $b->id)->count());
        $this->assertSame(2, Article::withoutInstanceScope()->count());
    }
}
