<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Integration\Referentiel;

use Modules\Core\Database\Scopes\InstanceScope;
use Modules\Menuiserie360\Domain\Client\Models\ClientMenuiserie;
use Modules\Referentiel360\Contracts\Party\PartyAttributesDto;
use Modules\Referentiel360\Contracts\Party\PartySource;

/**
 * Lot 1.a (ADR-030) — PartySource backfill des clients Menuiserie360.
 *
 * Taggée `referentiel.party_source` (cf. ServiceProvider, uniquement si
 * Referentiel360 est activé). Itère les clients de l'instance et yield des
 * DTO neutres pour la réconciliation idempotente (`referentiel:backfill-tiers`).
 */
final class MenuiserieClientPartySource implements PartySource
{
    public function __construct(
        private readonly MenuiseriePartyMapper $mapper,
    ) {}

    public function linkType(): string
    {
        return 'mnu.client';
    }

    /**
     * @return iterable<int, PartyAttributesDto>
     */
    public function each(int $instanceId): iterable
    {
        $clients = ClientMenuiserie::query()
            ->withoutGlobalScope(InstanceScope::class)
            ->where('instance_id', $instanceId)
            ->orderBy('id')
            ->cursor();

        foreach ($clients as $client) {
            yield $this->mapper->fromClient($client);
        }
    }
}
