<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Referentiel360\Contracts\Party\PartyAttributesDto;
use Modules\Referentiel360\Contracts\Party\PartyWriter;
use Modules\Referentiel360\Domain\Party\Models\Party;
use Modules\Referentiel360\Domain\Party\Models\PartyLink;
use Modules\Referentiel360\Domain\Party\Services\BackfillTiersService;
use Modules\Referentiel360\Domain\Party\Services\PartyMatcher;
use Modules\Referentiel360\Tests\Support\FakePartySource;
use Modules\Referentiel360\Tests\TestCase;

/**
 * ADR-030 — Backfill rejouable : 2e passage = 0 doublon.
 */
final class BackfillIdempotencyTest extends TestCase
{
    private int $instanceId;

    protected function setUp(): void
    {
        parent::setUp();
        $root = $this->makeRootInstance();
        $this->instanceId = (int) $root->id;
        CurrentInstance::set($root);
    }

    public function test_second_run_creates_no_duplicate(): void
    {
        $source = new FakePartySource('mnu.client', [
            $this->instanceId => [
                new PartyAttributesDto(localId: 1, isCustomer: true, displayName: 'Alpha', email: 'a@x.ci'),
                new PartyAttributesDto(localId: 2, isCustomer: true, displayName: 'Beta', email: 'b@x.ci'),
            ],
        ]);

        $service = new BackfillTiersService([$source], app(PartyMatcher::class), app(PartyWriter::class));

        $first = $service->run([$this->instanceId]);
        $this->assertSame(2, $first->created);
        $this->assertSame(2, Party::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
        $this->assertSame(2, PartyLink::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());

        $second = $service->run([$this->instanceId]);
        $this->assertSame(0, $second->created, '2e passage ne crée aucun party');
        $this->assertSame(2, $second->matched, '2e passage matche via le lien existant');

        // Aucun doublon en base.
        $this->assertSame(2, Party::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
        $this->assertSame(2, PartyLink::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
    }

    public function test_dry_run_writes_nothing(): void
    {
        $source = new FakePartySource('mnu.client', [
            $this->instanceId => [
                new PartyAttributesDto(localId: 1, isCustomer: true, displayName: 'Alpha', email: 'a@x.ci'),
            ],
        ]);

        $service = new BackfillTiersService([$source], app(PartyMatcher::class), app(PartyWriter::class));
        $report = $service->run([$this->instanceId], dryRun: true);

        $this->assertSame(1, $report->created);
        $this->assertSame(0, $report->linked);
        $this->assertSame(0, Party::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
    }
}
