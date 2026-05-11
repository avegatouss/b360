<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Finance\Listeners;

use Illuminate\Support\Facades\DB;
use Modules\Menuiserie360\Domain\Chantier\Events\ChantierTermine;
use Modules\Menuiserie360\Domain\Finance\Actions\CreateMenuiserieInvoiceAction;
use Modules\Menuiserie360\Domain\Sales\Models\BonCommande;

/**
 * P3-1 — Listener Finance qui crée la facture de solde à la clôture
 * d'un chantier (ChantierTermine event).
 *
 * Idempotence : `CreateMenuiserieInvoiceAction::executeSolde` court-circuite
 * si une facture SOLDE existe déjà pour ce BC, donc plusieurs dispatch
 * du même event (clôture/réouverture) ne créent qu'une seule facture.
 *
 * Calcule automatiquement : montant_ttc_bc - somme(acomptes facturés).
 */
final class CreateSoldeOnChantierTermine
{
    public function __construct(
        private readonly CreateMenuiserieInvoiceAction $createInvoiceAction,
    ) {}

    public function handle(ChantierTermine $event): void
    {
        $bc = BonCommande::query()
            ->where('instance_id', $event->instanceId)
            ->where('id', $event->bcId)
            ->first();

        if ($bc === null) {
            return;
        }

        DB::transaction(function () use ($bc) {
            $this->createInvoiceAction->executeSolde($bc);
        });
    }
}
