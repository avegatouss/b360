<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Finance\Listeners;

use Illuminate\Support\Facades\DB;
use Modules\Menuiserie360\Domain\Commercial\Events\DevisAccepte;
use Modules\Menuiserie360\Domain\Finance\Actions\CreateMenuiserieInvoiceAction;
use Modules\Menuiserie360\Domain\Sales\Models\BonCommande;

/**
 * P2-14 — Listener Finance qui crée la facture acompte à l'acceptation
 * d'un devis (DevisAccepte event).
 *
 * Décision v1.3 §1.4 : BC-Finance autonome → on utilise
 * CreateMenuiserieInvoiceAction (modèles natifs Menuiserie360),
 * PAS d'appel au contrat Eshop360.
 *
 * Lie la facture créée au BC via `mnu_bon_commandes.facture_acompte_id`.
 */
final class CreateAcompteOnDevisAccepte
{
    public function __construct(
        private readonly CreateMenuiserieInvoiceAction $createInvoiceAction,
    ) {}

    public function handle(DevisAccepte $event): void
    {
        $bc = BonCommande::query()
            ->where('instance_id', $event->instanceId)
            ->where('id', $event->bcId)
            ->first();

        if ($bc === null) {
            return;
        }

        DB::transaction(function () use ($bc) {
            $invoice = $this->createInvoiceAction->executeAcompte($bc);

            // Lier la facture acompte au BC (pour traçabilité).
            if ((int) $bc->getAttribute('facture_acompte_id') === 0
                || $bc->getAttribute('facture_acompte_id') === null) {
                $bc->setAttribute('facture_acompte_id', $invoice->getKey());
                $bc->save();
            }
        });
    }
}
