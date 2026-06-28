<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Contracts\Finance;

/**
 * DTO immutable d'ENTRÉE neutre (ADR-031 / Lot 3).
 *
 * Poussé par les modules L3 vers `FinanceWriter` (et émis par les
 * `FinanceSource` pour le backfill). Referentiel360 ignore l'origine : il ne
 * reçoit qu'un état financier normalisé + l'identifiant local de la facture.
 *
 * Idempotence : le seul critère est le lien (`linkType` + `localId`) — pas de
 * dédup. Le Writer fait un FULL REFRESH (last-write-wins) des montants/statut.
 *
 * Les montants sont en `string` (cohérent avec le cast décimal du modèle, pas de
 * float intermédiaire). `partyLinkType` + `partyLocalId` servent à résoudre le
 * tiers golden via le PartyReader (lien interne au module Referentiel360).
 */
final readonly class FinanceAttributesDto
{
    public function __construct(
        public int $localId,
        public string $documentNumber,
        public string $docType = 'invoice',         // invoice | credit_note
        public string $currency = 'XOF',
        public string $amountHt = '0',
        public string $amountTax = '0',
        public string $amountTtc = '0',
        public string $paidAmount = '0',
        public ?string $issuedAt = null,
        public ?string $dueDate = null,
        public bool $isCancelled = false,
        public ?string $partyLinkType = null,
        public ?int $partyLocalId = null,
        public string $sourceModule = '',
    ) {}
}
