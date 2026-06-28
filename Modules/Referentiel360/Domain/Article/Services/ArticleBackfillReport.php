<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Domain\Article\Services;

/**
 * Rapport mutable accumulé pendant le backfill articles (par instance puis
 * agrégé).
 *
 * Pas de notion de `review`/collision ici (matching lien-only, jamais de dédup
 * par code) : on ne compte que créations / matchs (lien existant) / liens écrits.
 */
final class ArticleBackfillReport
{
    public int $created = 0;

    public int $matched = 0;

    public int $linked = 0;

    public function recordCreated(): void
    {
        $this->created++;
    }

    public function recordMatched(): void
    {
        $this->matched++;
    }

    public function recordLinked(): void
    {
        $this->linked++;
    }
}
