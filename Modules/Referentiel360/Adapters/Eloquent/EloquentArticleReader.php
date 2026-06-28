<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Adapters\Eloquent;

use Modules\Core\Database\Scopes\InstanceScope;
use Modules\Referentiel360\Contracts\Article\ArticleDto;
use Modules\Referentiel360\Contracts\Article\ArticleReader;
use Modules\Referentiel360\Domain\Article\Models\Article;
use Modules\Referentiel360\Domain\Article\Models\ArticleLink;

/**
 * Implémentation Eloquent du {@see ArticleReader} (ADR-030 / Lot 2).
 *
 * Unique endroit (avec Writer/Resolver) où les modèles `ref_article*` sont
 * importés. Toutes les requêtes filtrent explicitement `instance_id` et
 * bypassent le global scope pour être indépendantes du CurrentInstance courant.
 */
final class EloquentArticleReader implements ArticleReader
{
    public function find(int $instanceId, int $articleId): ?ArticleDto
    {
        $article = Article::query()
            ->withoutGlobalScope(InstanceScope::class)
            ->where('instance_id', $instanceId)
            ->whereKey($articleId)
            ->first();

        return $article ? self::mapToDto($article) : null;
    }

    public function getByLink(int $instanceId, string $linkType, int $localId): ?ArticleDto
    {
        $link = ArticleLink::withoutInstanceScope()
            ->where('instance_id', $instanceId)
            ->where('linkable_type', $linkType)
            ->where('linkable_id', $localId)
            ->first();

        if ($link === null) {
            return null;
        }

        return $this->find($instanceId, (int) $link->getAttribute('article_id'));
    }

    public static function mapToDto(Article $a): ArticleDto
    {
        return new ArticleDto(
            id: (int) $a->getAttribute('id'),
            instanceId: (int) $a->getAttribute('instance_id'),
            articleUid: (string) $a->getAttribute('article_uid'),
            code: (string) $a->getAttribute('code'),
            label: (string) $a->getAttribute('label'),
            articleType: (string) $a->getAttribute('article_type'),
            unit: self::str($a->getAttribute('unit')),
            salePrice: self::str($a->getAttribute('sale_price')),
            taxRate: self::str($a->getAttribute('tax_rate')),
            categoryLabel: self::str($a->getAttribute('category_label')),
            description: self::str($a->getAttribute('description')),
            isActive: (bool) $a->getAttribute('is_active'),
            sourceModule: self::str($a->getAttribute('source_module')),
        );
    }

    private static function str(mixed $value): ?string
    {
        return $value !== null ? (string) $value : null;
    }
}
