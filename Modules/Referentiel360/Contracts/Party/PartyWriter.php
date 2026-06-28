<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Contracts\Party;

/**
 * Contrat d'ÉCRITURE du golden record tiers (ADR-030).
 *
 * Les modules L3 POUSSENT leurs tiers via `upsertFromModule`. La résolution
 * (lien existant → dédup → création) est déléguée au {@see PartyResolver}.
 *
 * Implémentation par défaut :
 * {@see \Modules\Referentiel360\Adapters\Eloquent\EloquentPartyWriter}.
 */
interface PartyWriter
{
    /**
     * Crée ou met à jour le golden record correspondant à l'objet local, et
     * garantit le lien (idempotent sur la clé unique du lien).
     */
    public function upsertFromModule(int $instanceId, string $linkType, PartyAttributesDto $attrs): PartyDto;

    /**
     * Crée (ou conserve) le lien entre un party et un objet local.
     */
    public function link(int $instanceId, int $partyId, string $linkType, int $localId): void;
}
