<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Domain\Article\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Traits\BelongsToInstance;

/**
 * ADR-030 / Lot 2 — Liaison polymorphe golden record article ↔ objet local L3.
 *
 * `linkable_type` est une short-key libre (`mnu.catalog_item`, `mnu.matiere`,
 * `eshop.product`), pas un morphTo Eloquent classique. Unicité (instance, type,
 * id) garantit qu'un objet local pointe au plus un article (idempotence backfill).
 *
 * @property int $id
 * @property int $instance_id
 * @property int $article_id
 * @property string $linkable_type
 * @property int $linkable_id
 */
class ArticleLink extends Model
{
    use BelongsToInstance;

    protected $table = 'ref_article_links';

    protected $fillable = [
        'instance_id',
        'article_id',
        'linkable_type',
        'linkable_id',
    ];

    protected $casts = [
        'article_id' => 'integer',
        'linkable_id' => 'integer',
    ];

    /**
     * @return BelongsTo<Article, $this>
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_id');
    }
}
