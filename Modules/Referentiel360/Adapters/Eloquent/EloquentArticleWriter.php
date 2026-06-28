<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Adapters\Eloquent;

use Illuminate\Support\Facades\DB;
use Modules\Core\Database\Scopes\InstanceScope;
use Modules\Referentiel360\Contracts\Article\ArticleAttributesDto;
use Modules\Referentiel360\Contracts\Article\ArticleDto;
use Modules\Referentiel360\Contracts\Article\ArticleResolver;
use Modules\Referentiel360\Contracts\Article\ArticleWriter;
use Modules\Referentiel360\Domain\Article\Events\ArticleUpserted;
use Modules\Referentiel360\Domain\Article\Models\Article;
use Modules\Referentiel360\Domain\Article\Models\ArticleLink;

/**
 * Implémentation Eloquent du {@see ArticleWriter} (ADR-030 / Lot 2).
 *
 * `upsertFromModule` délègue la décision (réutiliser via lien / créer) à
 * l'ArticleResolver, puis met à jour le golden record de façon non destructive
 * (ne vide jamais un champ déjà renseigné), garantit le lien (idempotent), et
 * émet ArticleUpserted.
 */
final class EloquentArticleWriter implements ArticleWriter
{
    public function __construct(private readonly ArticleResolver $resolver) {}

    public function upsertFromModule(int $instanceId, string $linkType, ArticleAttributesDto $attrs): ArticleDto
    {
        return DB::transaction(function () use ($instanceId, $linkType, $attrs): ArticleDto {
            $resolved = $this->resolver->resolve($instanceId, $linkType, $attrs);

            $created = false;

            if ($resolved === null) {
                $article = $this->createArticle($instanceId, $attrs);
                $created = true;
            } else {
                $article = Article::query()
                    ->withoutGlobalScope(InstanceScope::class)
                    ->where('instance_id', $instanceId)
                    ->whereKey($resolved->id)
                    ->firstOrFail();
                $this->mergeInto($article, $attrs);
            }

            $this->ensureLink($instanceId, (int) $article->getKey(), $linkType, $attrs->localId);

            ArticleUpserted::dispatch(
                $instanceId,
                (int) $article->getKey(),
                $linkType,
                $attrs->localId,
                $created,
            );

            return EloquentArticleReader::mapToDto($article->refresh());
        });
    }

    public function link(int $instanceId, int $articleId, string $linkType, int $localId): void
    {
        $this->ensureLink($instanceId, $articleId, $linkType, $localId);
    }

    private function createArticle(int $instanceId, ArticleAttributesDto $attrs): Article
    {
        $article = new Article;
        $article->instance_id = $instanceId;
        $article->code = $attrs->code !== '' ? $attrs->code : 'ART';
        $article->label = $attrs->label !== '' ? $attrs->label : 'Article';
        $article->article_type = $attrs->articleType !== '' ? $attrs->articleType : 'produit';
        $article->unit = $attrs->unit;
        $article->sale_price = $attrs->salePrice;
        $article->tax_rate = $attrs->taxRate;
        $article->category_label = $attrs->categoryLabel;
        $article->description = $attrs->description;
        $article->is_active = $attrs->isActive;
        $article->source_module = $attrs->sourceModule;
        $article->save();

        return $article;
    }

    /**
     * Fusion non destructive : complète les trous, ne remplace jamais une valeur
     * existante par un trou.
     */
    private function mergeInto(Article $article, ArticleAttributesDto $attrs): void
    {
        $this->fillIfEmpty($article, 'code', $attrs->code !== '' ? $attrs->code : null);
        $this->fillIfEmpty($article, 'label', $attrs->label !== '' ? $attrs->label : null);
        $this->fillIfEmpty($article, 'unit', $attrs->unit);
        $this->fillIfEmpty($article, 'sale_price', $attrs->salePrice);
        $this->fillIfEmpty($article, 'tax_rate', $attrs->taxRate);
        $this->fillIfEmpty($article, 'category_label', $attrs->categoryLabel);
        $this->fillIfEmpty($article, 'description', $attrs->description);
        $this->fillIfEmpty($article, 'source_module', $attrs->sourceModule);

        if ($article->isDirty()) {
            $article->save();
        }
    }

    private function fillIfEmpty(Article $article, string $column, ?string $value): void
    {
        if ($value === null) {
            return;
        }
        $current = $article->getAttribute($column);
        if ($current === null || $current === '') {
            $article->setAttribute($column, $value);
        }
    }

    private function ensureLink(int $instanceId, int $articleId, string $linkType, int $localId): void
    {
        $exists = ArticleLink::withoutInstanceScope()
            ->where('instance_id', $instanceId)
            ->where('linkable_type', $linkType)
            ->where('linkable_id', $localId)
            ->exists();

        if ($exists) {
            return;
        }

        ArticleLink::create([
            'instance_id' => $instanceId,
            'article_id' => $articleId,
            'linkable_type' => $linkType,
            'linkable_id' => $localId,
        ]);
    }
}
