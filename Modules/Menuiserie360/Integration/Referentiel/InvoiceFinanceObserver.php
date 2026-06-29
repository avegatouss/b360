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
 * LAST-WRITE-WINS CORRECT (MAJEUR 2) : on NE capture PAS le DTO au moment de
 * l'event (snapshot pris hors transaction → périmé si plusieurs afterCommit
 * s'exécutent dans le désordre, ou si `paid` baisse via avoir/annulation). On ne
 * retient que l'identifiant ; le DTO est construit DANS le `afterCommit` à partir
 * de l'état COMMITTÉ rechargé (`fresh()`), ce qui élimine la classe de bug.
 * Si la facture n'existe plus (supprimée), on skippe silencieusement.
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
        $invoiceId = (int) $invoice->getKey();

        DB::afterCommit(function () use ($instanceId, $invoiceId): void {
            try {
                // Recharge l'état committé le plus récent (élimine le snapshot périmé).
                $fresh = MenuiserieInvoice::query()->find($invoiceId);

                if ($fresh === null) {
                    return; // facture supprimée entre-temps : rien à pousser.
                }

                $this->writer->upsertFromModule($instanceId, 'mnu.invoice', $this->mapper->fromInvoice($fresh));
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }
}
