<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Domain\Party\Services;

/**
 * Rapport mutable accumulé pendant le backfill (par instance puis agrégé).
 */
final class BackfillReport
{
    public int $created = 0;

    public int $matched = 0;

    public int $linked = 0;

    /** @var array<int, array{instance_id:int, link_type:string, local_id:int, rule:?string, reason:?string}> */
    public array $reviews = [];

    public function recordCreated(): void
    {
        $this->created++;
    }

    public function recordMatched(): void
    {
        $this->matched++;
    }

    public function recordLinked(): void
    {
        $this->linked++;
    }

    public function recordReview(int $instanceId, string $linkType, int $localId, ?string $rule, ?string $reason): void
    {
        $this->reviews[] = [
            'instance_id' => $instanceId,
            'link_type' => $linkType,
            'local_id' => $localId,
            'rule' => $rule,
            'reason' => $reason,
        ];
    }

    public function reviewCount(): int
    {
        return count($this->reviews);
    }
}
