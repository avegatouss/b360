<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Referentiel360\Contracts\Article\ArticleAttributesDto;
use Modules\Referentiel360\Contracts\Article\ArticleWriter;
use Modules\Referentiel360\Domain\Article\Models\Article;
use Modules\Referentiel360\Domain\Article\Models\ArticleLink;
use Modules\Referentiel360\Domain\Article\Services\ArticleMatcher;
use Modules\Referentiel360\Domain\Article\Services\BackfillArticlesService;
use Modules\Referentiel360\Tests\Support\FakeArticleSource;
use Modules\Referentiel360\Tests\TestCase;

/**
 * ADR-030 / Lot 2 — Backfill articles rejouable : 2e passage = 0 doublon ;
 * no-op si 0 source.
 */
final class ArticleBackfillIdempotencyTest extends TestCase
{
    private int $instanceId;

    protected function setUp(): void
    {
        parent::setUp();
        $root = $this->makeRootInstance();
        $this->instanceId = (int) $root->id;
        CurrentInstance::set($root);
    }

    public function test_second_run_creates_no_duplicate(): void
    {
        $source = new FakeArticleSource('mnu.catalog_item', [
            $this->instanceId => [
                new ArticleAttributesDto(localId: 1, code: 'A-1', label: 'Alpha'),
                new ArticleAttributesDto(localId: 2, code: 'A-2', label: 'Beta'),
            ],
        ]);

        $service = new BackfillArticlesService([$source], app(ArticleMatcher::class), app(ArticleWriter::class));

        $first = $service->run([$this->instanceId]);
        $this->assertSame(2, $first->created);
        $this->assertSame(2, Article::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
        $this->assertSame(2, ArticleLink::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());

        $second = $service->run([$this->instanceId]);
        $this->assertSame(0, $second->created, '2e passage ne crée aucun article');
        $this->assertSame(2, $second->matched, '2e passage matche via le lien existant');

        $this->assertSame(2, Article::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
        $this->assertSame(2, ArticleLink::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
    }

    public function test_dry_run_writes_nothing(): void
    {
        $source = new FakeArticleSource('mnu.catalog_item', [
            $this->instanceId => [
                new ArticleAttributesDto(localId: 1, code: 'A-1', label: 'Alpha'),
            ],
        ]);

        $service = new BackfillArticlesService([$source], app(ArticleMatcher::class), app(ArticleWriter::class));
        $report = $service->run([$this->instanceId], dryRun: true);

        $this->assertSame(1, $report->created);
        $this->assertSame(0, $report->linked);
        $this->assertSame(0, Article::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
    }

    public function test_no_source_is_noop(): void
    {
        // 0 ArticleSource ⇒ backfill no-op (aucune écriture, rapport vide).
        $service = new BackfillArticlesService([], app(ArticleMatcher::class), app(ArticleWriter::class));
        $report = $service->run([$this->instanceId]);

        $this->assertSame(0, $report->created);
        $this->assertSame(0, $report->matched);
        $this->assertSame(0, $report->linked);
        $this->assertSame(0, Article::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
    }
}
