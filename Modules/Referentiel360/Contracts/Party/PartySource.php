<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Contracts\Party;

/**
 * INVERSION DE DÉPENDANCE (ADR-030 §3).
 *
 * Interface L2 *définie ici* mais *implémentée par les modules L3* (Lots 1.a /
 * 1.b). Chaque module métier expose ses tiers sous forme de `PartyAttributesDto`
 * neutres. Le backfill (commande `referentiel:backfill-tiers`) itère toutes les
 * sources taggées `referentiel.party_source` ; Referentiel360 ne lit JAMAIS
 * `eshop_*` / `mnu_*` directement.
 *
 * 0 source enregistrée ⇒ backfill no-op. Les tests utilisent une FakePartySource.
 */
interface PartySource
{
    /**
     * Short-key du type de lien produit (`mnu.client`, `eshop.customer`, …).
     */
    public function linkType(): string;

    /**
     * Itère les tiers d'une instance sous forme de DTO neutres (avec localId).
     *
     * @return iterable<int, PartyAttributesDto>
     */
    public function each(int $instanceId): iterable;
}
