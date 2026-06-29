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
 * l'ArticleResolver, puis rafraîchit le golden record selon la politique
 * « refresh-if-present » (R-505) : une valeur source non-vide écrase l'existant ;
 * une source vide/null ne vide jamais un champ déjà renseigné. `label` n'est
 * donc jamais vidé et `source_module` conserve l'origine du golden. Le lien est
 * garanti (idempotent) et ArticleUpserted est émis.
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
     * Fusion « refresh-if-present » (R-505) : chaque champ catalogue est écrasé
     * dès que la valeur source est non-vide ; une source vide/null laisse
     * l'existant intact (jamais de remise à vide). `label` n'est donc jamais
     * vidé. `code` (clé d'identité du golden) reste hors politique : il n'est
     * complété que s'il est vide. `source_module` n'est pas rafraîchi (on
     * conserve l'origine du golden).
     */
    private function mergeInto(Article $article, ArticleAttributesDto $attrs): void
    {
        $this->fillIfEmpty($article, 'code', $attrs->code !== '' ? $attrs->code : null);

        $this->refreshIfPresent($article, 'label', $attrs->label);
        $this->refreshIfPresent($article, 'unit', $attrs->unit);
        $this->refreshIfPresent($article, 'sale_price', $attrs->salePrice);
        $this->refreshIfPresent($article, 'tax_rate', $attrs->taxRate);
        $this->refreshIfPresent($article, 'category_label', $attrs->categoryLabel);
        $this->refreshIfPresent($article, 'description', $attrs->description);

        if ($article->isDirty()) {
            $article->save();
        }
    }

    /**
     * Écrase la colonne si la valeur source est présente (non-null et non-vide
     * après trim) ; sinon laisse l'existant intact. Cœur de la politique R-505.
     */
    private function refreshIfPresent(Article $article, string $column, ?string $value): void
    {
        if ($value === null || trim($value) === '') {
            return;
        }
        $article->setAttribute($column, $value);
    }

    /**
     * Complète une colonne seulement si elle est vide (non destructif). Réservé
     * à `code`, clé d'identité du golden, hors politique refresh-if-present.
     */
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
