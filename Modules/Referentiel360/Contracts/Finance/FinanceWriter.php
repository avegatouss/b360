<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Contracts\Finance;

/**
 * Contrat d'ÉCRITURE du registre financier miroir (ADR-031 / Lot 3).
 *
 * Les modules L3 POUSSENT l'état courant de leurs factures via
 * `upsertFromModule`. Le Writer fait un FULL REFRESH (last-write-wins) des
 * champs miroir, prend un `lockForUpdate` sur la ligne existante (concurrence des
 * paiements, zone L1), dérive `due_amount` + `status_normalized`, résout
 * `party_id` via le PartyReader, garantit le lien (idempotent), et émet
 * FinanceDocumentUpserted.
 *
 * INVARIANT ABSOLU : aucune écriture vers `mnu_*` / `eshop_*` (registre miroir).
 *
 * Implémentation par défaut :
 * {@see \Modules\Referentiel360\Adapters\Eloquent\EloquentFinanceWriter}.
 */
interface FinanceWriter
{
    /**
     * Crée ou rafraîchit intégralement le document miroir correspondant à la
     * facture locale, et garantit le lien (idempotent sur la clé unique du lien).
     */
    public function upsertFromModule(int $instanceId, string $linkType, FinanceAttributesDto $attrs): FinanceDto;
}
