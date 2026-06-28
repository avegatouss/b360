<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Modules\Core\Support\CurrentInstance;
use Modules\Referentiel360\Contracts\Party\PartyAttributesDto;
use Modules\Referentiel360\Contracts\Party\PartyWriter;
use Modules\Referentiel360\Domain\Party\Events\PartyUpserted;
use Modules\Referentiel360\Domain\Party\Models\Party;
use Modules\Referentiel360\Domain\Party\Models\PartyLink;
use Modules\Referentiel360\Tests\TestCase;

/**
 * Nominal + déduplication (ADR-030 / Lot 1).
 */
final class PartyResolverUpsertTest extends TestCase
{
    private int $instanceId;

    private PartyWriter $writer;

    protected function setUp(): void
    {
        parent::setUp();
        $root = $this->makeRootInstance();
        $this->instanceId = (int) $root->id;
        CurrentInstance::set($root);
        $this->writer = app(PartyWriter::class);
    }

    public function test_nominal_upsert_creates_golden_record_and_link(): void
    {
        Event::fake([PartyUpserted::class]);

        $dto = $this->writer->upsertFromModule($this->instanceId, 'mnu.client', new PartyAttributesDto(
            localId: 42,
            isCustomer: true,
            displayName: 'KHOGA SARL',
            email: 'Contact@Khoga.CI',
            phone: '+225 07 00 00 00 00',
            sourceModule: 'menuiserie',
        ));

        $this->assertTrue($dto->isCustomer);
        $this->assertSame('contact@khoga.ci', $dto->email);   // normalisé
        $this->assertSame('2250700000000', $dto->phone);       // digits only
        $this->assertNotEmpty($dto->partyUid);

        $this->assertSame(1, Party::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());

        $link = PartyLink::withoutInstanceScope()
            ->where('instance_id', $this->instanceId)
            ->where('linkable_type', 'mnu.client')
            ->where('linkable_id', 42)
            ->first();
        $this->assertNotNull($link);
        $this->assertSame($dto->id, (int) $link->getAttribute('party_id'));

        Event::assertDispatched(PartyUpserted::class, fn (PartyUpserted $e): bool => $e->created === true && $e->localId === 42 && $e->linkType === 'mnu.client');
    }

    public function test_dedup_by_email_merges_two_modules_into_one_party_two_links(): void
    {
        // Même email côté Menuiserie (client) et Eshop (customer) → 1 party, 2 liens.
        $a = $this->writer->upsertFromModule($this->instanceId, 'mnu.client', new PartyAttributesDto(
            localId: 1,
            isCustomer: true,
            displayName: 'Jean Dupont',
            email: 'jean@dupont.ci',
        ));

        $b = $this->writer->upsertFromModule($this->instanceId, 'eshop.customer', new PartyAttributesDto(
            localId: 99,
            isCustomer: true,
            displayName: 'J. Dupont',
            email: 'JEAN@DUPONT.CI ',
        ));

        $this->assertSame($a->id, $b->id, 'Même email → même golden record');
        $this->assertSame(1, Party::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
        $this->assertSame(2, PartyLink::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
    }

    public function test_supplier_without_code_is_created(): void
    {
        // eshop_suppliers n'a pas de `code` : le ref ne l'exige pas.
        $dto = $this->writer->upsertFromModule($this->instanceId, 'eshop.supplier', new PartyAttributesDto(
            localId: 7,
            isSupplier: true,
            displayName: 'Fournisseur Alu',
            sourceModule: 'eshop',
        ));

        $this->assertTrue($dto->isSupplier);
        $this->assertSame(1, Party::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
    }

    public function test_role_promotion_on_second_upsert(): void
    {
        // Un party client devient aussi fournisseur via un 2e lien sur le même email.
        $this->writer->upsertFromModule($this->instanceId, 'mnu.client', new PartyAttributesDto(
            localId: 1,
            isCustomer: true,
            displayName: 'Polyvalent',
            email: 'poly@x.ci',
        ));

        $b = $this->writer->upsertFromModule($this->instanceId, 'mnu.supplier', new PartyAttributesDto(
            localId: 2,
            isSupplier: true,
            displayName: 'Polyvalent',
            email: 'poly@x.ci',
        ));

        $this->assertTrue($b->isCustomer);
        $this->assertTrue($b->isSupplier);
        $this->assertSame(1, Party::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
    }
}
