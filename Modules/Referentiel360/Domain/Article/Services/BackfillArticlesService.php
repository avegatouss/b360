<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Domain\Article\Services;

use Modules\Referentiel360\Contracts\Article\ArticleSource;
use Modules\Referentiel360\Contracts\Article\ArticleWriter;

/**
 * ADR-030 / Lot 2 — Orchestration du backfill (réconciliation) des articles.
 *
 * Itère les `ArticleSource` taggées (implémentées par les L3 en Lots 2.a/2.b).
 * 0 source ⇒ no-op. Pour chaque article d'une instance : upsert via
 * ArticleWriter (idempotent sur le lien unique).
 *
 * Matching LIEN-ONLY : un 2ᵉ passage retombe sur les liens existants (rule
 * `link`) et ne crée aucun doublon. AUCUNE fusion par code (univers disjoints).
 */
final class BackfillArticlesService
{
    /**
     * @param  iterable<int, ArticleSource>  $sources
     */
    public function __construct(
        private readonly iterable $sources,
        private readonly ArticleMatcher $matcher,
        private readonly ArticleWriter $writer,
    ) {}

    /**
     * @param  array<int, int>  $instanceIds
     */
    public function run(array $instanceIds, bool $dryRun = false): ArticleBackfillReport
    {
        $report = new ArticleBackfillReport;

        foreach ($this->sources as $source) {
            $linkType = $source->linkType();

            foreach ($instanceIds as $instanceId) {
                foreach ($source->each($instanceId) as $attrs) {
                    $match = $this->matcher->match($instanceId, $linkType, $attrs);

                    if ($match->articleId !== null) {
                        $report->recordMatched();
                    } else {
                        $report->recordCreated();
                    }

                    if (! $dryRun) {
                        $this->writer->upsertFromModule($instanceId, $linkType, $attrs);
                        $report->recordLinked();
                    }
                }
            }
        }

        return $report;
    }
}
