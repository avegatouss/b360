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
 * LAST-WRITE-WINS CORRECT (MAJEUR 2) : on NE capture PAS le DTO au moment de
 * l'event (snapshot pris hors transaction → périmé si plusieurs afterCommit
 * s'exécutent dans le désordre, ou si `paid` baisse). On ne retient que
 * l'identifiant ; le DTO est construit DANS le `afterCommit` à partir de l'état
 * COMMITTÉ rechargé (`find()`), ce qui élimine la classe de bug. Si l'invoice
 * n'existe plus (supprimée), on skippe silencieusement.
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
        $invoiceId = (int) $invoice->getKey();

        DB::afterCommit(function () use ($instanceId, $invoiceId): void {
            try {
                // Recharge l'état committé le plus récent (élimine le snapshot périmé).
                $fresh = Invoice::query()->find($invoiceId);

                if ($fresh === null) {
                    return; // invoice supprimée entre-temps : rien à pousser.
                }

                $this->writer->upsertFromModule($instanceId, 'eshop.invoice', $this->mapper->fromInvoice($fresh));
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }
}
