<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Tests\Support;

use Modules\Referentiel360\Contracts\Finance\FinanceAttributesDto;
use Modules\Referentiel360\Contracts\Finance\FinanceSource;

/**
 * Source de test (remplace les FinanceSource L3 des Lots 3.a/3.b).
 *
 * Renvoie des DTO neutres pré-définis par instance — Referentiel360 ne touche
 * jamais `eshop_*`/`mnu_*` dans ses tests.
 */
final class FakeFinanceSource implements FinanceSource
{
    /**
     * @param  array<int, array<int, FinanceAttributesDto>>  $byInstance  instanceId => [attrs, ...]
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
