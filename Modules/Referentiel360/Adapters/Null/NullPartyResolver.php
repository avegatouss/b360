<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Adapters\Null;

use Modules\Referentiel360\Contracts\Party\PartyAttributesDto;
use Modules\Referentiel360\Contracts\Party\PartyDto;
use Modules\Referentiel360\Contracts\Party\PartyResolver;

/**
 * ADR-030 §4 « activable » — binding utilisé quand Referentiel360 est désactivé.
 *
 * Renvoie toujours null : les modules L3 retombent sur leurs données locales
 * (autonomie ADR-023), sans aucun accès à `ref_parties`.
 *
 * Note Lot 1 : le binding par défaut reste EloquentPartyResolver (module
 * activé). NullPartyResolver est fourni pour les Lots 1.a/1.b qui décideront du
 * binding selon l'état d'activation.
 */
final class NullPartyResolver implements PartyResolver
{
    public function resolve(int $instanceId, string $linkType, PartyAttributesDto $attrs): ?PartyDto
    {
        return null;
    }
}
