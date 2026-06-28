<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Contracts\Finance;

/**
 * INVERSION DE DÉPENDANCE (ADR-031 / Lot 3).
 *
 * Interface L2 *définie ici* mais *implémentée par les modules L3* (Lots 3.a /
 * 3.b). Chaque module métier expose ses factures sous forme de
 * `FinanceAttributesDto` neutres. Le backfill (commande
 * `referentiel:backfill-finance`) itère toutes les sources taggées
 * `referentiel.finance_source` ; Referentiel360 ne lit JAMAIS `eshop_*` /
 * `mnu_*` directement.
 *
 * 0 source enregistrée ⇒ backfill no-op. Les tests utilisent une FakeFinanceSource.
 */
interface FinanceSource
{
    /**
     * Short-key du type de lien facture (`mnu.invoice`, `eshop.invoice`).
     */
    public function linkType(): string;

    /**
     * Itère les factures d'une instance sous forme de DTO neutres (avec localId).
     *
     * @return iterable<int, FinanceAttributesDto>
     */
    public function each(int $instanceId): iterable;
}
