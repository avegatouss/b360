<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Integration\Referentiel;

use Modules\Core\Database\Scopes\InstanceScope;
use Modules\Menuiserie360\Domain\Catalog\Models\CatalogItem;
use Modules\Referentiel360\Contracts\Article\ArticleAttributesDto;
use Modules\Referentiel360\Contracts\Article\ArticleSource;

/**
 * Lot 2.a (ADR-030) — ArticleSource backfill du catalogue Menuiserie360.
 *
 * Taggée `referentiel.article_source` (cf. ServiceProvider, uniquement si
 * Referentiel360 est activé). Itère les items catalogue de l'instance et yield
 * des DTO neutres pour la réconciliation idempotente
 * (`referentiel:backfill-articles`).
 */
final class MenuiserieCatalogItemArticleSource implements ArticleSource
{
    public function __construct(
        private readonly MenuiserieArticleMapper $mapper,
    ) {}

    public function linkType(): string
    {
        return 'mnu.catalog_item';
    }

    /**
     * @return iterable<int, ArticleAttributesDto>
     */
    public function each(int $instanceId): iterable
    {
        $items = CatalogItem::query()
            ->withoutGlobalScope(InstanceScope::class)
            ->where('instance_id', $instanceId)
            ->orderBy('id')
            ->cursor();

        foreach ($items as $item) {
            yield $this->mapper->fromCatalogItem($item);
        }
    }
}
