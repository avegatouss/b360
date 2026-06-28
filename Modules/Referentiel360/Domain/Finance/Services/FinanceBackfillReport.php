<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Domain\Finance\Services;

/**
 * Rapport mutable accumulé pendant le backfill finance (par instance puis agrégé).
 *
 * Pas de notion de `review`/collision (matching lien-only, jamais de dédup) : on
 * ne compte que créations / matchs (lien existant rafraîchi) / liens écrits.
 */
final class FinanceBackfillReport
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
