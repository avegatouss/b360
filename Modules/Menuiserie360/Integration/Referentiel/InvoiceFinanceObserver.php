<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Integration\Referentiel;

use Illuminate\Support\Facades\DB;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;
use Modules\Referentiel360\Contracts\Finance\FinanceWriter;

/**
 * Lot 3.a (ADR-031, ZONE L1) — Observer best-effort MenuiserieInvoice → Referentiel360.
 *
 * Sur created/updated, pousse l'état COURANT de la facture vers le registre
 * financier miroir APRÈS commit de la transaction Menuiserie (DB::afterCommit),
 * de façon best-effort : toute exception est rapportée (report) mais JAMAIS
 * propagée — la création/màj de la facture ne doit jamais échouer à cause du
 * référentiel (ADR-023 : Menuiserie reste autonome).
 *
 * FULL REFRESH : un encaissement met à jour `paid_amount` sur la facture
 * (RecordPaymentAction) ⇒ l'event `updated` capte le changement et re-push.
 * Le Writer écrase tous les champs miroir (last-write-wins) et dérive le statut.
 *
 * Attaché uniquement si Referentiel360 est activé (cf. ServiceProvider).
 */
final class InvoiceFinanceObserver
{
    public function __construct(
        private readonly FinanceWriter $writer,
        private readonly MenuiserieFinanceMapper $mapper,
    ) {}

    public function created(MenuiserieInvoice $invoice): void
    {
        $this->push($invoice);
    }

    public function updated(MenuiserieInvoice $invoice): void
    {
        $this->push($invoice);
    }

    private function push(MenuiserieInvoice $invoice): void
    {
        $instanceId = (int) $invoice->getAttribute('instance_id');
        $attrs = $this->mapper->fromInvoice($invoice);

        DB::afterCommit(function () use ($instanceId, $attrs): void {
            try {
                $this->writer->upsertFromModule($instanceId, 'mnu.invoice', $attrs);
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }
}
