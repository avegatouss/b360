<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Referentiel360\Contracts\Party\PartyAttributesDto;
use Modules\Referentiel360\Domain\Party\Models\Party;
use Modules\Referentiel360\Domain\Party\Models\PartyLink;
use Modules\Referentiel360\Domain\Party\Services\PartyMatcher;
use Modules\Referentiel360\Tests\TestCase;

/**
 * Couverture de l'ordre de priorité du matcher (ADR-030, L2 sensible).
 */
final class PartyMatcherTest extends TestCase
{
    private int $instanceId;

    private PartyMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();
        $root = $this->makeRootInstance();
        $this->instanceId = (int) $root->id;
        CurrentInstance::set($root);
        $this->matcher = app(PartyMatcher::class);
    }

    /**
     * @param  array<string, mixed>  $attrs
     */
    private function makeParty(array $attrs): Party
    {
        return Party::create(array_merge([
            'instance_id' => $this->instanceId,
            'display_name' => 'X',
        ], $attrs));
    }

    public function test_existing_link_wins_priority_1(): void
    {
        $p = $this->makeParty(['email' => 'a@x.ci']);
        PartyLink::create([
            'instance_id' => $this->instanceId,
            'party_id' => $p->id,
            'linkable_type' => 'mnu.client',
            'linkable_id' => 10,
        ]);

        // Email différent mais lien existant → match par lien.
        $r = $this->matcher->match($this->instanceId, 'mnu.client', new PartyAttributesDto(
            localId: 10, email: 'other@x.ci',
        ));
        $this->assertSame($p->id, $r->partyId);
        $this->assertSame('link', $r->rule);
    }

    public function test_email_match_priority_2(): void
    {
        $p = $this->makeParty(['email' => 'mail@x.ci']);
        $r = $this->matcher->match($this->instanceId, 'mnu.client', new PartyAttributesDto(
            localId: 1, email: 'MAIL@x.ci',
        ));
        $this->assertSame($p->id, $r->partyId);
        $this->assertSame('email', $r->rule);
    }

    public function test_phone_match_priority_3(): void
    {
        $p = $this->makeParty(['phone' => '2250700000000']);
        $r = $this->matcher->match($this->instanceId, 'mnu.client', new PartyAttributesDto(
            localId: 1, phone: '+225 07-00-00-00-00',
        ));
        $this->assertSame($p->id, $r->partyId);
        $this->assertSame('phone', $r->rule);
    }

    public function test_rccm_then_nif_priority_4(): void
    {
        $byRccm = $this->makeParty(['tax_id_rccm' => 'RCCM-123']);
        $r1 = $this->matcher->match($this->instanceId, 'mnu.supplier', new PartyAttributesDto(
            localId: 1, taxIdRccm: 'RCCM-123',
        ));
        $this->assertSame($byRccm->id, $r1->partyId);
        $this->assertSame('rccm', $r1->rule);

        $byNif = $this->makeParty(['tax_id_nif' => 'NIF-999']);
        $r2 = $this->matcher->match($this->instanceId, 'mnu.supplier', new PartyAttributesDto(
            localId: 2, taxIdNif: 'NIF-999',
        ));
        $this->assertSame($byNif->id, $r2->partyId);
        $this->assertSame('nif', $r2->rule);
    }

    public function test_no_signal_returns_none(): void
    {
        $r = $this->matcher->match($this->instanceId, 'mnu.client', new PartyAttributesDto(
            localId: 1, displayName: 'Anonyme',
        ));
        $this->assertNull($r->partyId);
        $this->assertFalse($r->review);
    }
}
