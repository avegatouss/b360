<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Contracts\Article;

/**
 * Contrat de RÉSOLUTION du golden record article (ADR-030 §4 « activable »).
 *
 * `resolve` cherche UNIQUEMENT un lien existant (idempotence du backfill) ;
 * sinon ⇒ null (le Writer crée un nouveau golden). AUCUNE déduplication par
 * code/label : les univers articles des deux modules sont disjoints. C'est le
 * point d'entrée que les modules L3 appellent toujours :
 *   - module Referentiel360 ACTIVÉ  → EloquentArticleResolver (golden record)
 *   - module Referentiel360 DÉSACTIVÉ → NullArticleResolver (fallback local)
 */
interface ArticleResolver
{
    public function resolve(int $instanceId, string $linkType, ArticleAttributesDto $attrs): ?ArticleDto;
}
