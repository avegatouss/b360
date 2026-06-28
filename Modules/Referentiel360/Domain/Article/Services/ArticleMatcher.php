<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Domain\Article\Services;

use Modules\Referentiel360\Contracts\Article\ArticleAttributesDto;
use Modules\Referentiel360\Domain\Article\Models\ArticleLink;

/**
 * ADR-030 / Lot 2 (L2 sensible) — Matching des articles, scopé `instance_id`.
 *
 * DÉCISION STRUCTURANTE (cf IMPACT_ANALYSIS) : match **PAR LIEN EXISTANT
 * UNIQUEMENT**. AUCUNE déduplication par code/label : les univers articles des
 * deux modules sont sémantiquement disjoints (menuiserie sur-mesure + BOM vs
 * produits SKU e-commerce). 1 row source = 1 article golden.
 *
 *   1. Lien fiable existant (ce linkType + localId a déjà un article_id) → match.
 *   2. Sinon → aucun match (nouveau golden).
 *
 * Toutes les requêtes filtrent explicitement l'instance et bypassent le global
 * scope pour rester indépendantes du CurrentInstance courant.
 */
final class ArticleMatcher
{
    public function match(int $instanceId, string $linkType, ArticleAttributesDto $attrs): ArticleMatchResult
    {
        $existingLink = ArticleLink::withoutInstanceScope()
            ->where('instance_id', $instanceId)
            ->where('linkable_type', $linkType)
            ->where('linkable_id', $attrs->localId)
            ->first();

        if ($existingLink !== null) {
            return ArticleMatchResult::matched((int) $existingLink->getAttribute('article_id'), 'link');
        }

        return ArticleMatchResult::none();
    }
}
