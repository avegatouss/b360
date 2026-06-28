<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Domain\Finance\Services;

use Modules\Referentiel360\Contracts\Finance\FinanceAttributesDto;
use Modules\Referentiel360\Domain\Finance\Models\FinanceDocumentLink;

/**
 * ADR-031 / Lot 3 (ZONE L1) — Matching des factures, scopé `instance_id`.
 *
 * DÉCISION STRUCTURANTE (cf ADR-031 / IMPACT_ANALYSIS) : match **PAR LIEN
 * EXISTANT UNIQUEMENT** (comme ArticleMatcher). AUCUNE déduplication : 1 facture
 * locale = 1 document miroir. Les architectures de facturation/paiement des deux
 * modules sont disjointes — aucune fusion.
 *
 *   1. Lien fiable existant (ce linkType + localId a déjà un document_id) → match.
 *   2. Sinon → aucun match (nouveau document miroir).
 *
 * Toutes les requêtes filtrent explicitement l'instance et bypassent le global
 * scope pour rester indépendantes du CurrentInstance courant.
 */
final class FinanceMatcher
{
    public function match(int $instanceId, string $linkType, FinanceAttributesDto $attrs): FinanceMatchResult
    {
        $existingLink = FinanceDocumentLink::withoutInstanceScope()
            ->where('instance_id', $instanceId)
            ->where('linkable_type', $linkType)
            ->where('linkable_id', $attrs->localId)
            ->first();

        if ($existingLink !== null) {
            return FinanceMatchResult::matched((int) $existingLink->getAttribute('document_id'), 'link');
        }

        return FinanceMatchResult::none();
    }
}
