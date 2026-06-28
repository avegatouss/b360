<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Adapters\Eloquent;

use Modules\Referentiel360\Contracts\Party\PartyAttributesDto;
use Modules\Referentiel360\Contracts\Party\PartyDto;
use Modules\Referentiel360\Contracts\Party\PartyResolver;
use Modules\Referentiel360\Domain\Party\Services\PartyMatcher;

/**
 * Implémentation Eloquent du {@see PartyResolver} (ADR-030 §4, module ACTIVÉ).
 *
 * Résout via le PartyMatcher : match franc → renvoie le PartyDto existant ;
 * collision ambiguë (`review`) ou aucun match → renvoie null (le Writer crée un
 * nouveau party). Le resolver ne crée jamais et n'écrit jamais : il décide.
 */
final class EloquentPartyResolver implements PartyResolver
{
    public function __construct(
        private readonly PartyMatcher $matcher,
        private readonly EloquentPartyReader $reader,
    ) {}

    public function resolve(int $instanceId, string $linkType, PartyAttributesDto $attrs): ?PartyDto
    {
        $result = $this->matcher->match($instanceId, $linkType, $attrs);

        if ($result->partyId === null) {
            // Aucun match OU collision ambiguë (review) → pas de fusion auto.
            return null;
        }

        return $this->reader->find($instanceId, $result->partyId);
    }
}
