<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Domain\Finance\Services;

use Modules\Referentiel360\Contracts\Finance\FinanceSource;
use Modules\Referentiel360\Contracts\Finance\FinanceWriter;

/**
 * ADR-031 / Lot 3 — Orchestration du backfill (resync) du registre financier.
 *
 * Itère les `FinanceSource` taggées (implémentées par les L3 en Lots 3.a/3.b).
 * 0 source ⇒ no-op. Pour chaque facture d'une instance : upsert via FinanceWriter
 * (idempotent sur le lien unique, FULL REFRESH des montants/statut).
 *
 * Garantit la convergence en cas de push best-effort raté : un 2ᵉ passage
 * retombe sur les liens existants (rule `link`) et rafraîchit sans doublon.
 */
final class BackfillFinanceService
{
    /**
     * @param  iterable<int, FinanceSource>  $sources
     */
    public function __construct(
        private readonly iterable $sources,
        private readonly FinanceMatcher $matcher,
        private readonly FinanceWriter $writer,
    ) {}

    /**
     * @param  array<int, int>  $instanceIds
     */
    public function run(array $instanceIds, bool $dryRun = false): FinanceBackfillReport
    {
        $report = new FinanceBackfillReport;

        foreach ($this->sources as $source) {
            $linkType = $source->linkType();

            foreach ($instanceIds as $instanceId) {
                foreach ($source->each($instanceId) as $attrs) {
                    $match = $this->matcher->match($instanceId, $linkType, $attrs);

                    if ($match->documentId !== null) {
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
