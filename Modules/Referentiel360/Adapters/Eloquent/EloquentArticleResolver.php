<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Adapters\Eloquent;

use Modules\Referentiel360\Contracts\Article\ArticleAttributesDto;
use Modules\Referentiel360\Contracts\Article\ArticleDto;
use Modules\Referentiel360\Contracts\Article\ArticleResolver;
use Modules\Referentiel360\Domain\Article\Services\ArticleMatcher;

/**
 * Implémentation Eloquent du {@see ArticleResolver} (ADR-030 §4, module ACTIVÉ).
 *
 * Résout via l'ArticleMatcher LIEN-ONLY : lien existant → renvoie l'ArticleDto ;
 * aucun lien → null (le Writer crée un nouveau golden). AUCUNE dédup par code :
 * les univers articles des deux modules sont disjoints. Le resolver ne crée
 * jamais et n'écrit jamais : il décide.
 */
final class EloquentArticleResolver implements ArticleResolver
{
    public function __construct(
        private readonly ArticleMatcher $matcher,
        private readonly EloquentArticleReader $reader,
    ) {}

    public function resolve(int $instanceId, string $linkType, ArticleAttributesDto $attrs): ?ArticleDto
    {
        $result = $this->matcher->match($instanceId, $linkType, $attrs);

        if ($result->articleId === null) {
            return null;
        }

        return $this->reader->find($instanceId, $result->articleId);
    }
}
