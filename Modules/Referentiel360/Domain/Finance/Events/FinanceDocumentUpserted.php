<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Domain\Finance\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * ADR-031 / Lot 3 — Émis par EloquentFinanceWriter après création / rafraîchissement
 * d'un document financier miroir. Non consommé en Lot 3 (socle).
 */
final readonly class FinanceDocumentUpserted
{
    use Dispatchable;

    public function __construct(
        public int $instanceId,
        public int $documentId,
        public string $linkType,
        public int $localId,
        public bool $created,
    ) {}
}
