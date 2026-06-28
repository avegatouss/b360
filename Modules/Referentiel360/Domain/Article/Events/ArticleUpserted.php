<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Domain\Article\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * ADR-030 / Lot 2 — Émis par EloquentArticleWriter après création / mise à jour
 * d'un golden record article. Non consommé en Lot 2 (base de la synchro inverse
 * future).
 */
final readonly class ArticleUpserted
{
    use Dispatchable;

    public function __construct(
        public int $instanceId,
        public int $articleId,
        public string $linkType,
        public int $localId,
        public bool $created,
    ) {}
}
