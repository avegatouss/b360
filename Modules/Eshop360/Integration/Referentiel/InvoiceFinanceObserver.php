<?php

declare(strict_types=1);

namespace Modules\Eshop360\Integration\Referentiel;

use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Domain\Finance\Models\Invoice;
use Modules\Referentiel360\Contracts\Finance\FinanceWriter;

/**
 * Lot 3.b (ADR-031) — Observer best-effort Invoice → Referentiel360 (Finance).
 *
 * Sur created/updated, pousse l'invoice vers le registre financier miroir APRÈS
 * commit de la transaction Eshop (DB::afterCommit), de façon best-effort : toute
 * exception est rapportée (report) mais JAMAIS propagée — la création/màj de la
 * facture Eshop ne doit jamais échouer à cause du référentiel (Eshop reste
 * autonome). Le Writer fait un FULL REFRESH (last-write-wins), donc tout update
 * de `paid_amount` re-pousse l'état courant complet.
 *
 * Attaché uniquement si Referentiel360 est activé (cf. ServiceProvider).
 */
final class InvoiceFinanceObserver
{
    public function __construct(
        private readonly FinanceWriter $writer,
        private readonly EshopFinanceMapper $mapper,
    ) {}

    public function created(Invoice $invoice): void
    {
        $this->push($invoice);
    }

    public function updated(Invoice $invoice): void
    {
        $this->push($invoice);
    }

    private function push(Invoice $invoice): void
    {
        $instanceId = (int) $invoice->getAttribute('instance_id');
        $attrs = $this->mapper->fromInvoice($invoice);

        DB::afterCommit(function () use ($instanceId, $attrs): void {
            try {
                $this->writer->upsertFromModule($instanceId, 'eshop.invoice', $attrs);
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }
}
