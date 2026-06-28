<?php

declare(strict_types=1);

namespace Modules\Eshop360\Integration\Referentiel;

use Modules\Core\Database\Scopes\InstanceScope;
use Modules\Eshop360\Database\Scopes\ChannelScope;
use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Referentiel360\Contracts\Party\PartyAttributesDto;
use Modules\Referentiel360\Contracts\Party\PartySource;

/**
 * Lot 1.b (ADR-030) — PartySource backfill des customers Eshop360.
 *
 * Taggée `referentiel.party_source` (cf. ServiceProvider, uniquement si
 * Referentiel360 est activé). Itère les customers de l'instance (TOUS canaux
 * confondus — 1 party par row) et yield des DTO neutres pour la réconciliation
 * idempotente (`referentiel:backfill-tiers`).
 *
 * Bypass InstanceScope ET ChannelScope : le backfill couvre toute l'instance
 * indépendamment du canal courant et de l'utilisateur authentifié.
 */
final class EshopCustomerPartySource implements PartySource
{
    public function __construct(
        private readonly EshopPartyMapper $mapper,
    ) {}

    public function linkType(): string
    {
        return 'eshop.customer';
    }

    /**
     * @return iterable<int, PartyAttributesDto>
     */
    public function each(int $instanceId): iterable
    {
        $customers = Customer::query()
            ->withoutGlobalScope(InstanceScope::class)
            ->withoutGlobalScope(ChannelScope::class)
            ->where('instance_id', $instanceId)
            ->orderBy('id')
            ->cursor();

        foreach ($customers as $customer) {
            yield $this->mapper->fromCustomer($customer);
        }
    }
}
