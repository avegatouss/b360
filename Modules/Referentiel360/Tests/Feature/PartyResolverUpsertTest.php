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

    public function test_reupsert_with_new_email_refreshes_golden(): void
    {
        // R-505 : une nouvelle valeur source non-vide écrase l'existant.
        $a = $this->writer->upsertFromModule($this->instanceId, 'mnu.client', new PartyAttributesDto(
            localId: 5,
            isCustomer: true,
            displayName: 'Awa Koné',
            email: 'awa@old.ci',
            phone: '+225 01 02 03 04 05',
        ));

        // Même objet local (match par lien) avec un email mis à jour.
        $b = $this->writer->upsertFromModule($this->instanceId, 'mnu.client', new PartyAttributesDto(
            localId: 5,
            isCustomer: true,
            displayName: 'Awa Koné',
            email: 'awa@new.ci',
        ));

        $this->assertSame($a->id, $b->id, 'Même golden (match par lien)');
        $this->assertSame('awa@new.ci', $b->email, 'R-505 : email source non-vide écrase l\'existant');
        $this->assertSame('2250102030405', $b->phone, 'Champ absent de la source 2 → conservé');
        $this->assertSame(1, Party::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
    }

    public function test_reupsert_with_empty_field_keeps_existing_value(): void
    {
        // R-505 : une source vide/null ne vide jamais un champ déjà renseigné.
        $a = $this->writer->upsertFromModule($this->instanceId, 'mnu.client', new PartyAttributesDto(
            localId: 8,
            isCustomer: true,
            displayName: 'Société Beta',
            email: 'beta@x.ci',
            phone: '+225 07 07 07 07 07',
            address: '12 rue des Jardins',
        ));

        $b = $this->writer->upsertFromModule($this->instanceId, 'mnu.client', new PartyAttributesDto(
            localId: 8,
            isCustomer: true,
            displayName: '',     // vide → display_name conservé
            email: null,         // null → email conservé
            phone: '   ',        // blanc → phone conservé (trim)
        ));

        $this->assertSame($a->id, $b->id);
        $this->assertSame('Société Beta', $b->displayName, 'R-505 : display_name jamais vidé');
        $this->assertSame('beta@x.ci', $b->email, 'R-505 : email conservé si source null');
        $this->assertSame('2250707070707', $b->phone, 'R-505 : phone conservé si source blanche');
        $this->assertSame('12 rue des Jardins', $b->address, 'Champ absent de la source 2 → conservé');
        $this->assertSame(1, Party::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
    }

    public function test_roles_stay_in_or_on_reupsert(): void
    {
        // R-505 : rôles jamais rétrogradés (OR). Customer re-poussé supplier → les deux.
        $a = $this->writer->upsertFromModule($this->instanceId, 'mnu.client', new PartyAttributesDto(
            localId: 21,
            isCustomer: true,
            displayName: 'Mixte SARL',
            email: 'mixte@x.ci',
        ));

        $b = $this->writer->upsertFromModule($this->instanceId, 'mnu.client', new PartyAttributesDto(
            localId: 21,
            isSupplier: true,    // isCustomer défaut false : ne doit PAS rétrograder
            displayName: 'Mixte SARL',
            email: 'mixte@x.ci',
        ));

        $this->assertSame($a->id, $b->id);
        $this->assertTrue($b->isCustomer, 'R-505 : rôle customer conservé (OR)');
        $this->assertTrue($b->isSupplier, 'R-505 : rôle supplier ajouté (OR)');
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
