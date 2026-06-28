<?php

declare(strict_types=1);

namespace Modules\Eshop360\Integration\Referentiel;

use Modules\Core\Database\Scopes\InstanceScope;
use Modules\Eshop360\Database\Scopes\ChannelScope;
use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Referentiel360\Contracts\Article\ArticleAttributesDto;
use Modules\Referentiel360\Contracts\Article\ArticleSource;

/**
 * Lot 2.b (ADR-030) — ArticleSource backfill des products Eshop360.
 *
 * Taggée `referentiel.article_source` (cf. ServiceProvider, uniquement si
 * Referentiel360 est activé). Itère les products de l'instance (TOUS canaux
 * confondus — 1 article par row) et yield des DTO neutres pour la réconciliation
 * idempotente (`referentiel:backfill-articles`).
 *
 * Bypass InstanceScope ET ChannelScope : le backfill couvre toute l'instance
 * indépendamment du canal courant et de l'utilisateur authentifié (sinon le
 * backfill CLI ne voit rien).
 */
final class EshopProductArticleSource implements ArticleSource
{
    public function __construct(
        private readonly EshopArticleMapper $mapper,
    ) {}

    public function linkType(): string
    {
        return 'eshop.product';
    }

    /**
     * @return iterable<int, ArticleAttributesDto>
     */
    public function each(int $instanceId): iterable
    {
        $products = Product::query()
            ->withoutGlobalScope(InstanceScope::class)
            ->withoutGlobalScope(ChannelScope::class)
            ->where('instance_id', $instanceId)
            ->orderBy('id')
            ->cursor();

        foreach ($products as $product) {
            yield $this->mapper->fromProduct($product);
        }
    }
}
