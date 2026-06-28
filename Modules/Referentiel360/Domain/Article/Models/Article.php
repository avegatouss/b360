<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Domain\Article\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Core\Database\Traits\BelongsToInstance;

/**
 * ADR-030 / Lot 2 — Golden record « article » (catalogue mince).
 *
 * Référentiel mince : identité catalogue partagée seulement. Le BOM, les
 * variations, le stock et la péremption restent dans les tables des modules L3.
 *
 * @property int $id
 * @property int $instance_id
 * @property string $article_uid
 * @property string $code
 * @property string $label
 * @property string $article_type
 * @property string|null $unit
 * @property string|null $sale_price
 * @property string|null $tax_rate
 * @property string|null $category_label
 * @property string|null $description
 * @property bool $is_active
 * @property string|null $source_module
 */
class Article extends Model
{
    use BelongsToInstance, SoftDeletes;

    protected $table = 'ref_articles';

    protected $fillable = [
        'instance_id',
        'article_uid',
        'code',
        'label',
        'article_type',
        'unit',
        'sale_price',
        'tax_rate',
        'category_label',
        'description',
        'is_active',
        'source_module',
    ];

    protected $casts = [
        'sale_price' => 'decimal:4',
        'tax_rate' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Article $article): void {
            if (empty($article->article_uid)) {
                $article->article_uid = (string) Str::ulid();
            }
        });
    }

    /**
     * @return HasMany<ArticleLink, $this>
     */
    public function links(): HasMany
    {
        return $this->hasMany(ArticleLink::class, 'article_id');
    }
}
