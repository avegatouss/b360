<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Contracts\Customer\CustomerDto;
use Modules\Eshop360\Contracts\Customer\CustomerReader;
use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Menuiserie360\Domain\Client\Contracts\ClientMenuiserieDto;
use Modules\Menuiserie360\Domain\Client\Contracts\ClientRepositoryContract;
use Modules\Menuiserie360\Domain\Client\Models\ClientMenuiserie;
use Modules\Menuiserie360\Domain\Client\Repositories\ClientMenuiserieRepository;
use Modules\Menuiserie360\Tests\TestCase;

/**
 * P1-5 — Tests ClientMenuiserieRepository.
 *
 * Note ADR-021 : ce test est l'un des SEULS endroits où le test Menuiserie360
 * touche à la base eshop_customers — c'est nécessaire pour valider l'ACL,
 * mais via le modèle Customer Eshop360 et seulement dans les Tests/ (les
 * tests structurels excluent ce dossier de leurs invariants).
 */
final class ClientMenuiserieRepositoryTest extends TestCase
{
    private ClientMenuiserieRepository $repository;

    private int $instanceId;

    protected function setUp(): void
    {
        parent::setUp();

        // R-403 — ce test exige Eshop360 actif (binding CustomerReader +
        // table eshop_customers chargée par les migrations du module). Si
        // Eshop360 est désactivé via modules_statuses.json, on skip pour
        // signaler explicitement la cause au lieu d'un BindingResolutionException
        // opaque. Voir docs/memory/OPEN_RISKS.md R-403 et ADR-021 §contraintes runtime.
        if (! $this->app->bound(CustomerReader::class)) {
            $this->markTestSkipped(
                'R-403 : Eshop360 désactivé — binding CustomerReader absent du container. '
                .'Réactiver Eshop360 dans modules_statuses.json pour exécuter ce test.'
            );
        }

        Cache::flush();

        $instance = $this->makeRootInstance();
        $this->instanceId = $instance->id;
        CurrentInstance::set($instance);

        // Le repository est résolu via DI → utilise le binding réel
        // (CustomerReader → EloquentCustomerReader).
        $this->repository = app(ClientMenuiserieRepository::class);
    }

    // ─── DI binding ─────────────────────────────────────────────

    public function test_client_repository_contract_is_bound_to_repository(): void
    {
        $resolved = app(ClientRepositoryContract::class);

        $this->assertInstanceOf(ClientMenuiserieRepository::class, $resolved);
    }

    public function test_repository_uses_customer_reader_via_di(): void
    {
        $repo = app(ClientMenuiserieRepository::class);

        // Sanity check : le repo est correctement instancié avec un CustomerReader
        $this->assertInstanceOf(ClientMenuiserieRepository::class, $repo);
        $this->assertInstanceOf(CustomerReader::class, app(CustomerReader::class));
    }

    // ─── find() ─────────────────────────────────────────────────

    public function test_find_returns_null_when_customer_does_not_exist(): void
    {
        $result = $this->repository->find($this->instanceId, 99999);

        $this->assertNull($result);
    }

    public function test_find_returns_dto_when_customer_exists_without_extension(): void
    {
        $customer = $this->makeEshopCustomer('CDF-001', 'Client Sans Extension');

        $result = $this->repository->find($this->instanceId, $customer->getKey());

        $this->assertInstanceOf(ClientMenuiserieDto::class, $result);
        $this->assertSame((int) $customer->getKey(), $result->id);
        $this->assertSame('CDF-001', $result->code);
        $this->assertSame('Client Sans Extension', $result->name);
        // Pas d'extension menuiserie → valeurs par défaut
        $this->assertNull($result->preferredContactMethod);
        $this->assertSame(0, $result->totalChantiersCount);
        $this->assertSame(0.0, $result->totalRevenueXof);
    }

    public function test_find_combines_customer_with_menuiserie_extension(): void
    {
        $customer = $this->makeEshopCustomer('CDF-002', 'Client Avec Extension');
        ClientMenuiserie::create([
            'instance_id' => $this->instanceId,
            'customer_id' => $customer->getKey(),
            'preferred_contact_method' => 'whatsapp',
            'total_chantiers_count' => 5,
            'total_revenue_xof' => 1250000.50,
        ]);

        $result = $this->repository->find($this->instanceId, $customer->getKey());

        $this->assertInstanceOf(ClientMenuiserieDto::class, $result);
        $this->assertSame('whatsapp', $result->preferredContactMethod);
        $this->assertSame(5, $result->totalChantiersCount);
        $this->assertSame(1250000.50, $result->totalRevenueXof);
    }

    // ─── canReference() ────────────────────────────────────────

    public function test_can_reference_returns_true_when_customer_exists(): void
    {
        $customer = $this->makeEshopCustomer('CDF-003', 'Client Référence');

        $this->assertTrue($this->repository->canReference($this->instanceId, $customer->getKey()));
    }

    public function test_can_reference_returns_false_for_unknown_customer(): void
    {
        $this->assertFalse($this->repository->canReference($this->instanceId, 99999));
    }

    public function test_can_reference_isolates_by_instance(): void
    {
        $customer = $this->makeEshopCustomer('CDF-004', 'Client Tenant A');

        $instanceB = \App\Instances\Instance::create([
            'name' => 'Tenant B',
            'slug' => 'tenant-b-'.uniqid(),
            'is_active' => true,
            'meta' => [],
        ]);

        // Le customer existe dans instance A, pas dans B
        $this->assertTrue($this->repository->canReference($this->instanceId, $customer->getKey()));
        $this->assertFalse($this->repository->canReference($instanceB->id, $customer->getKey()));
    }

    // ─── withMenuiserieHistory() ───────────────────────────────

    public function test_with_menuiserie_history_yields_clients_with_chantiers(): void
    {
        $clientA = $this->makeEshopCustomer('CDF-WMH-A', 'Client Avec Historique');
        $clientB = $this->makeEshopCustomer('CDF-WMH-B', 'Client Sans Historique');

        ClientMenuiserie::create([
            'instance_id' => $this->instanceId,
            'customer_id' => $clientA->getKey(),
            'total_chantiers_count' => 3,
            'total_revenue_xof' => 500000,
        ]);
        ClientMenuiserie::create([
            'instance_id' => $this->instanceId,
            'customer_id' => $clientB->getKey(),
            'total_chantiers_count' => 0, // ← exclu du résultat
            'total_revenue_xof' => 0,
        ]);

        $results = iterator_to_array($this->repository->withMenuiserieHistory($this->instanceId));

        $this->assertCount(1, $results);
        $this->assertSame('CDF-WMH-A', $results[0]->code);
        $this->assertSame(3, $results[0]->totalChantiersCount);
    }

    // ─── fromCustomerDto() ─────────────────────────────────────

    public function test_from_customer_dto_returns_immutable_dto(): void
    {
        $customerDto = new CustomerDto(
            id: 42,
            instanceId: $this->instanceId,
            channelId: null,
            userId: null,
            groupId: null,
            code: 'CONV-001',
            name: 'Client Conversion',
            email: 'conv@test.com',
            phone: null,
            address: null,
            city: null,
            country: null,
            companyName: null,
            taxNumber: null,
            walletBalance: 0.0,
            creditLimit: 0.0,
            isActive: true,
        );

        $result = $this->repository->fromCustomerDto($customerDto, [
            'preferred_contact_method' => 'sms',
            'total_chantiers_count' => 2,
            'total_revenue_xof' => 300000,
        ]);

        $this->assertInstanceOf(ClientMenuiserieDto::class, $result);
        $this->assertSame(42, $result->id);
        $this->assertSame('CONV-001', $result->code);
        $this->assertSame('sms', $result->preferredContactMethod);
        $this->assertSame(2, $result->totalChantiersCount);
        $this->assertSame(300000.0, $result->totalRevenueXof);
    }

    // ─── Helper ────────────────────────────────────────────────

    /**
     * Crée un Customer Eshop360 directement (utilisé pour driver les tests
     * du repository — c'est le canonical setup, pas une violation ADR-021
     * car les tests sont exclus des invariants structurels).
     */
    private function makeEshopCustomer(string $code, string $name): Customer
    {
        return Customer::withoutGlobalScopes()->create([
            'instance_id' => $this->instanceId,
            'code' => $code,
            'name' => $name,
            'email' => null,
            'is_active' => true,
        ]);
    }
}
