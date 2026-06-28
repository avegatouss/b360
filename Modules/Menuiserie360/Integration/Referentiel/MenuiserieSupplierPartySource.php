<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Integration\Referentiel;

use Modules\Core\Database\Scopes\InstanceScope;
use Modules\Menuiserie360\Domain\Purchasing\Models\Fournisseur;
use Modules\Referentiel360\Contracts\Party\PartyAttributesDto;
use Modules\Referentiel360\Contracts\Party\PartySource;

/**
 * Lot 1.a (ADR-030) — PartySource backfill des fournisseurs Menuiserie360.
 *
 * Taggée `referentiel.party_source` (cf. ServiceProvider, uniquement si
 * Referentiel360 est activé). Itère les fournisseurs de l'instance et yield des
 * DTO neutres pour la réconciliation idempotente (`referentiel:backfill-tiers`).
 */
final class MenuiserieSupplierPartySource implements PartySource
{
    public function __construct(
        private readonly MenuiseriePartyMapper $mapper,
    ) {}

    public function linkType(): string
    {
        return 'mnu.supplier';
    }

    /**
     * @return iterable<int, PartyAttributesDto>
     */
    public function each(int $instanceId): iterable
    {
        $fournisseurs = Fournisseur::query()
            ->withoutGlobalScope(InstanceScope::class)
            ->where('instance_id', $instanceId)
            ->orderBy('id')
            ->cursor();

        foreach ($fournisseurs as $fournisseur) {
            yield $this->mapper->fromFournisseur($fournisseur);
        }
    }
}
