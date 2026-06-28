<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Domain\Party\Services;

use Modules\Referentiel360\Contracts\Party\PartySource;
use Modules\Referentiel360\Contracts\Party\PartyWriter;

/**
 * ADR-030 / Lot 1 — Orchestration du backfill (réconciliation) des tiers.
 *
 * Itère les `PartySource` taggées (implémentées par les L3 en Lots 1.a/1.b).
 * 0 source ⇒ no-op. Pour chaque tiers d'une instance :
 *   - collision ambiguë → rapport `review`, AUCUNE écriture ;
 *   - sinon → upsert via PartyWriter (idempotent sur le lien unique).
 *
 * Idempotent : un 2ᵉ passage retombe sur les liens existants (rule `link`) et
 * ne crée aucun doublon.
 */
final class BackfillTiersService
{
    /**
     * @param  iterable<int, PartySource>  $sources
     */
    public function __construct(
        private readonly iterable $sources,
        private readonly PartyMatcher $matcher,
        private readonly PartyWriter $writer,
    ) {}

    /**
     * @param  array<int, int>  $instanceIds
     */
    public function run(array $instanceIds, bool $dryRun = false): BackfillReport
    {
        $report = new BackfillReport;

        foreach ($this->sources as $source) {
            $linkType = $source->linkType();

            foreach ($instanceIds as $instanceId) {
                foreach ($source->each($instanceId) as $attrs) {
                    $match = $this->matcher->match($instanceId, $linkType, $attrs);

                    if ($match->review) {
                        $report->recordReview($instanceId, $linkType, $attrs->localId, $match->rule, $match->reason);

                        continue;
                    }

                    if ($match->partyId !== null) {
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
