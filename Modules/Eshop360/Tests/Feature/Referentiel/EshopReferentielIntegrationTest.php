<?php

declare(strict_types=1);

namespace Modules\Eshop360\Tests\Feature\Referentiel;

use App\Instances\Instance;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Eshop360\Domain\Purchasing\Models\Supplier;
use Modules\Eshop360\Integration\Referentiel\CustomerReferentielObserver;
use Modules\Eshop360\Integration\Referentiel\EshopCustomerPartySource;
use Modules\Eshop360\Integration\Referentiel\EshopPartyMapper;
use Modules\Eshop360\Tests\TestCase;
use Modules\Referentiel360\Contracts\Party\PartyAttributesDto;
use Modules\Referentiel360\Contracts\Party\PartyDto;
use Modules\Referentiel360\Contracts\Party\PartyWriter;

/**
 * Lot 1.b (ADR-030) — Intégration Eshop360 → Referentiel360.
 *
 * Couvre : push observer post-commit (customer + supplier), best-effort
 * (PartyWriter qui throw ne casse pas la création), PartySource backfill et
 * la normalisation pays.
 *
 * Note RefreshDatabase + afterCommit : on installe un transaction manager de
 * test ({@see ImmediateAfterCommitTransactionsManager}) qui exécute les
 * callbacks afterCommit au commit de la transaction métier imbriquée (niveau 1).
 */
final class EshopReferentielIntegrationTest extends TestCase
{
    private Instance $instance;

    private int $instanceId;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        // Hub admin authentifié : ChannelScope ne fail-close pas, channel_id
        // auto-injecté null (création autorisée pour un hub admin sans canal).
        [$this->instance] = $this->setUpInstanceWithAdmin();
        $this->instanceId = (int) $this->instance->id;
        CurrentInstance::set($this->instance);

        // Force l'exécution des afterCommit au commit vers le niveau 1 (cf. classe).
        $manager = new ImmediateAfterCommitTransactionsManager;
        foreach (['sqlite', 'system'] as $name) {
            DB::connection($name)->setTransactionManager($manager);
        }
        $this->app->instance('db.transactions', $manager);
    }

    public function test_creating_customer_pushes_one_party_and_one_link(): void
    {
        $customer = DB::transaction(fn (): Customer => Customer::create([
            'instance_id' => $this->instanceId,
            'code' => 'CUS-REF-001',
            'name' => 'Clinique Sainte Marie',
            'company_name' => 'Clinique Sainte Marie SARL',
            'email' => 'contact@clinique.ci',
            'phone' => '+225 07 00 00 00 00',
            'country' => 'CI',
            'is_active' => true,
        ]));

        $this->assertSame(1, DB::table('ref_parties')->where('instance_id', $this->instanceId)->count());

        $link = DB::table('ref_party_links')
            ->where('instance_id', $this->instanceId)
            ->where('linkable_type', 'eshop.customer')
            ->where('linkable_id', $customer->getKey())
            ->first();

        $this->assertNotNull($link);
    }

    public function test_creating_supplier_pushes_one_party_and_supplier_link(): void
    {
        $supplier = DB::transaction(fn (): Supplier => Supplier::create([
            'instance_id' => $this->instanceId,
            'name' => 'Alu CI',
            'company' => 'Alu Cote Ivoire SA',
            'email' => 'ventes@aluci.ci',
            'phone' => '+225 27 00 00 00 00',
            'country' => 'CI',
            'is_active' => true,
        ]));

        $this->assertSame(1, DB::table('ref_parties')->where('instance_id', $this->instanceId)->count());

        $link = DB::table('ref_party_links')
            ->where('instance_id', $this->instanceId)
            ->where('linkable_type', 'eshop.supplier')
            ->where('linkable_id', $supplier->getKey())
            ->first();

        $this->assertNotNull($link);
    }

    public function test_push_is_best_effort_when_party_writer_throws(): void
    {
        // PartyWriter qui throw : la création customer doit réussir, l'exception avalée.
        $this->app->instance(PartyWriter::class, new class implements PartyWriter
        {
            public function upsertFromModule(int $instanceId, string $linkType, PartyAttributesDto $attrs): PartyDto
            {
                throw new \RuntimeException('Referentiel indisponible');
            }

            public function link(int $instanceId, int $partyId, string $linkType, int $localId): void
            {
                throw new \RuntimeException('Referentiel indisponible');
            }
        });

        // Réattacher l'observer avec le writer mocké (boot a câblé l'ancien).
        Customer::observe($this->app->make(CustomerReferentielObserver::class));

        $customer = DB::transaction(fn (): Customer => Customer::create([
            'instance_id' => $this->instanceId,
            'code' => 'CUS-BE-001',
            'name' => 'Survivor',
            'is_active' => true,
        ]));

        // Le customer existe malgré l'échec du référentiel.
        $this->assertDatabaseHas('eshop_customers', [
            'id' => $customer->getKey(),
            'name' => 'Survivor',
        ]);
        // Aucun party créé (writer a échoué silencieusement).
        $this->assertSame(0, DB::table('ref_parties')->where('instance_id', $this->instanceId)->count());
    }

    public function test_customer_party_source_yields_expected_dto(): void
    {
        Customer::create([
            'instance_id' => $this->instanceId,
            'code' => 'CUS-SRC-001',
            'name' => 'Source SARL',
            'company_name' => 'Source SARL',
            'email' => 'src@x.ci',
            'phone' => '0102030405',
            'country' => 'CI',
            'is_active' => true,
        ]);

        $source = new EshopCustomerPartySource(new EshopPartyMapper);

        $this->assertSame('eshop.customer', $source->linkType());

        $dtos = iterator_to_array($source->each($this->instanceId));
        $this->assertCount(1, $dtos);

        /** @var PartyAttributesDto $dto */
        $dto = $dtos[0];
        $this->assertTrue($dto->isCustomer);
        $this->assertSame('entreprise', $dto->personType);
        $this->assertSame('Source SARL', $dto->displayName);
        $this->assertSame('Source SARL', $dto->legalName);
        $this->assertSame('src@x.ci', $dto->email);
        $this->assertSame('eshop', $dto->sourceModule);
    }

    public function test_normalize_country_maps_to_iso2_or_falls_back(): void
    {
        $mapper = new EshopPartyMapper;

        // Code ISO-2 minuscule → majuscule.
        $fr = $mapper->fromCustomer(new Customer(['name' => 'X', 'country' => 'fr']));
        $this->assertSame('FR', $fr->country);

        // Libellé non ISO-2 → fallback 'CI'.
        $fallback = $mapper->fromCustomer(new Customer(['name' => 'X', 'country' => 'Côte d ivoire']));
        $this->assertSame('CI', $fallback->country);

        // Null → fallback 'CI'.
        $nullCountry = $mapper->fromCustomer(new Customer(['name' => 'X']));
        $this->assertSame('CI', $nullCountry->country);
    }
}
