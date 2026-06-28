<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Adapters\Null;

use Modules\Referentiel360\Contracts\Article\ArticleAttributesDto;
use Modules\Referentiel360\Contracts\Article\ArticleDto;
use Modules\Referentiel360\Contracts\Article\ArticleResolver;

/**
 * ADR-030 §4 « activable » — binding utilisé quand Referentiel360 est désactivé.
 *
 * Renvoie toujours null : les modules L3 retombent sur leurs données locales
 * (autonomie ADR-023), sans aucun accès à `ref_articles`.
 *
 * Note Lot 2 : le binding par défaut reste EloquentArticleResolver (module
 * activé). NullArticleResolver est fourni pour les Lots 2.a/2.b qui décideront
 * du binding selon l'état d'activation.
 */
final class NullArticleResolver implements ArticleResolver
{
    public function resolve(int $instanceId, string $linkType, ArticleAttributesDto $attrs): ?ArticleDto
    {
        return null;
    }
}
