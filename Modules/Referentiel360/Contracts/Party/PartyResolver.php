<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Contracts\Party;

/**
 * Contrat de RÉSOLUTION du golden record tiers (ADR-030, §4 « activable »).
 *
 * `resolve` cherche d'abord un lien existant ; sinon délègue au matcher
 * (dédup) ; sinon crée un nouveau party. C'est le point d'entrée que les
 * modules L3 appellent toujours :
 *   - module Referentiel360 ACTIVÉ  → EloquentPartyResolver (golden record)
 *   - module Referentiel360 DÉSACTIVÉ → NullPartyResolver (fallback local)
 */
interface PartyResolver
{
    public function resolve(int $instanceId, string $linkType, PartyAttributesDto $attrs): ?PartyDto;
}
