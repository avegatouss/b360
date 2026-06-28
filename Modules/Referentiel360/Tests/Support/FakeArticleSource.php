<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Tests\Support;

use Modules\Referentiel360\Contracts\Article\ArticleAttributesDto;
use Modules\Referentiel360\Contracts\Article\ArticleSource;

/**
 * Source de test (remplace les ArticleSource L3 des Lots 2.a/2.b).
 *
 * Renvoie des DTO neutres pré-définis par instance — Referentiel360 ne touche
 * jamais `eshop_*`/`mnu_*` dans ses tests.
 */
final class FakeArticleSource implements ArticleSource
{
    /**
     * @param  array<int, array<int, ArticleAttributesDto>>  $byInstance  instanceId => [attrs, ...]
     */
    public function __construct(
        private readonly string $linkType,
        private readonly array $byInstance,
    ) {}

    public function linkType(): string
    {
        return $this->linkType;
    }

    public function each(int $instanceId): iterable
    {
        return $this->byInstance[$instanceId] ?? [];
    }
}
