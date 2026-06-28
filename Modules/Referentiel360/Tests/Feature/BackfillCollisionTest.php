<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Referentiel360\Contracts\Party\PartyAttributesDto;
use Modules\Referentiel360\Contracts\Party\PartyWriter;
use Modules\Referentiel360\Domain\Party\Models\Party;
use Modules\Referentiel360\Domain\Party\Services\BackfillTiersService;
use Modules\Referentiel360\Domain\Party\Services\PartyMatcher;
use Modules\Referentiel360\Tests\Support\FakePartySource;
use Modules\Referentiel360\Tests\TestCase;

/**
 * ADR-030 — Collision ambiguë (>1 candidat) ⇒ rapport `review`, PAS de fusion auto.
 */
final class BackfillCollisionTest extends TestCase
{
    private int $instanceId;

    protected function setUp(): void
    {
        parent::setUp();
        $root = $this->makeRootInstance();
        $this->instanceId = (int) $root->id;
        CurrentInstance::set($root);
    }

    public function test_two_distinct_parties_sharing_email_trigger_review_not_merge(): void
    {
        $writer = app(PartyWriter::class);

        // Deux parties DISTINCTES partageant un email (état legacy possible en
        // base). On les crée directement pour reproduire la collision.
        $p1 = Party::create([
            'instance_id' => $this->instanceId,
            'is_customer' => true,
            'display_name' => 'Homonyme 1',
            'email' => 'dup@x.ci',
        ]);
        $p2 = Party::create([
            'instance_id' => $this->instanceId,
            'is_customer' => true,
            'display_name' => 'Homonyme 2',
            'email' => 'dup@x.ci',
        ]);
        $this->assertNotSame($p1->id, $p2->id);

        // Un 3e tiers entrant avec le même email → collision (2 candidats).
        $source = new FakePartySource('eshop.customer', [
            $this->instanceId => [
                new PartyAttributesDto(localId: 500, isCustomer: true, displayName: 'Incoming', email: 'dup@x.ci'),
            ],
        ]);

        $service = new BackfillTiersService([$source], app(PartyMatcher::class), $writer);
        $report = $service->run([$this->instanceId]);

        $this->assertSame(0, $report->created, 'Pas de fusion ni de création auto sur collision');
        $this->assertSame(0, $report->linked);
        $this->assertSame(1, $report->reviewCount());
        $this->assertSame('email', $report->reviews[0]['rule']);
        $this->assertSame(500, $report->reviews[0]['local_id']);

        // Toujours 2 parties (l'entrant n'a pas été matché ni créé).
        $this->assertSame(2, Party::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
    }
}
