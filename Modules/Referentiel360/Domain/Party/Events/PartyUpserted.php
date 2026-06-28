<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Domain\Party\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * ADR-030 — Émis par EloquentPartyWriter après création / mise à jour d'un
 * golden record. Non consommé en Lot 1 (base de la synchro inverse future).
 */
final readonly class PartyUpserted
{
    use Dispatchable;

    public function __construct(
        public int $instanceId,
        public int $partyId,
        public string $linkType,
        public int $localId,
        public bool $created,
    ) {}
}
