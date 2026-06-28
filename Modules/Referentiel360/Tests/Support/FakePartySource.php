<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Tests\Support;

use Modules\Referentiel360\Contracts\Party\PartyAttributesDto;
use Modules\Referentiel360\Contracts\Party\PartySource;

/**
 * Source de test (remplace les PartySource L3 des Lots 1.a/1.b).
 *
 * Renvoie des DTO neutres pré-définis par instance — Referentiel360 ne touche
 * jamais `eshop_*`/`mnu_*` dans ses tests.
 */
final class FakePartySource implements PartySource
{
    /**
     * @param  array<int, array<int, PartyAttributesDto>>  $byInstance  instanceId => [attrs, ...]
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
