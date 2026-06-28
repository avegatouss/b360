<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Contracts\Finance;

/**
 * DTO immutable de LECTURE du document financier miroir (ADR-031 / Lot 3).
 *
 * Surface publique consommée par les modules L3 / le reporting. Ajout d'un champ
 * optionnel = non-breaking ; retrait / changement de type = breaking (ADR de
 * remplacement). Les montants sont exposés en `string` (cohérent avec le cast
 * décimal du modèle, pas de perte de précision).
 */
final readonly class FinanceDto
{
    public function __construct(
        public int $id,
        public int $instanceId,
        public string $documentUid,
        public ?int $partyId,
        public string $docType,
        public string $documentNumber,
        public string $currency,
        public string $amountHt,
        public string $amountTax,
        public string $amountTtc,
        public string $paidAmount,
        public string $dueAmount,
        public string $statusNormalized,
        public bool $isCancelled,
        public ?string $issuedAt,
        public ?string $dueDate,
        public string $sourceModule,
    ) {}
}
