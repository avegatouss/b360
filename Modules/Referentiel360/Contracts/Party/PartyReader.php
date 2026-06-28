<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Contracts\Party;

/**
 * Contrat de LECTURE du golden record tiers (ADR-030).
 *
 * Implémentation par défaut :
 * {@see \Modules\Referentiel360\Adapters\Eloquent\EloquentPartyReader}.
 *
 * Toutes les méthodes sont scopées par instance.
 */
interface PartyReader
{
    public function find(int $instanceId, int $partyId): ?PartyDto;

    /**
     * Résout un party à partir d'un lien module local (short-key + id local).
     */
    public function getByLink(int $instanceId, string $linkType, int $localId): ?PartyDto;

    /**
     * Recherche par identité normalisée (email puis phone). Vide si aucun critère.
     *
     * @return array<int, PartyDto>
     */
    public function searchByIdentity(int $instanceId, ?string $email, ?string $phone): array;
}
