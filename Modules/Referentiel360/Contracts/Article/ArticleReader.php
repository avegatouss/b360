<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Contracts\Article;

/**
 * Contrat de LECTURE du golden record article (ADR-030 / Lot 2).
 *
 * Implémentation par défaut :
 * {@see \Modules\Referentiel360\Adapters\Eloquent\EloquentArticleReader}.
 *
 * Toutes les méthodes sont scopées par instance.
 */
interface ArticleReader
{
    public function find(int $instanceId, int $articleId): ?ArticleDto;

    /**
     * Résout un article à partir d'un lien module local (short-key + id local).
     */
    public function getByLink(int $instanceId, string $linkType, int $localId): ?ArticleDto;
}
