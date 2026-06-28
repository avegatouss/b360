<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Contracts\Article;

/**
 * INVERSION DE DÉPENDANCE (ADR-030 §3 / Lot 2).
 *
 * Interface L2 *définie ici* mais *implémentée par les modules L3* (Lots 2.a /
 * 2.b). Chaque module métier expose ses articles sous forme de
 * `ArticleAttributesDto` neutres. Le backfill (commande
 * `referentiel:backfill-articles`) itère toutes les sources taggées
 * `referentiel.article_source` ; Referentiel360 ne lit JAMAIS `eshop_*` /
 * `mnu_*` directement.
 *
 * 0 source enregistrée ⇒ backfill no-op. Les tests utilisent une FakeArticleSource.
 */
interface ArticleSource
{
    /**
     * Short-key du type de lien produit (`mnu.catalog_item`, `mnu.matiere`,
     * `eshop.product`).
     */
    public function linkType(): string;

    /**
     * Itère les articles d'une instance sous forme de DTO neutres (avec localId).
     *
     * @return iterable<int, ArticleAttributesDto>
     */
    public function each(int $instanceId): iterable;
}
