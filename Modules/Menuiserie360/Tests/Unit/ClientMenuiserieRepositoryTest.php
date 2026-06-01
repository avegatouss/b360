<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Support\CurrentInstance;
use Modules\Menuiserie360\Domain\Client\Contracts\ClientMenuiserieDto;
use Modules\Menuiserie360\Domain\Client\Contracts\ClientRepositoryContract;
use Modules\Menuiserie360\Domain\Client\Models\ClientMenuiserie;
use Modules\Menuiserie360\Domain\Client\Repositories\ClientMenuiserieRepository;
use Modules\Menuiserie360\Tests\TestCase;

final class ClientMenuiserieRepositoryTest extends TestCase
{
    private ClientMenuiserieRepository $repository;

    private int $instanceId;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $instance = $this->makeRootInstance();
        $this->instanceId = $instance->id;
        CurrentInstance::set($instance);

        $this->repository = app(ClientMenuiserieRepository::class);
    }

    public function test_client_repository_contract_is_bound_to_repository(): void
    {
        $resolved = app(ClientRepositoryContract::class);

        $this->assertInstanceOf(ClientMenuiserieRepository::class, $resolved);
    }

    public function test_repository_resolves_without_eshop360_contracts(): void
    {
        $this->assertInstanceOf(ClientMenuiserieRepository::class, app(ClientMenuiserieRepository::class));
    }

    public function test_find_returns_null_when_client_does_not_exist(): void
    {
        $result = $this->repository->find($this->instanceId, 99999);

        $this->assertNull($result);
    }

    public function test_find_returns_native_client_dto(): void
    {
        $client = $this->makeClient('CLI-001', ['raison_sociale' => 'Client Autonome']);

        $result = $this->repository->find($this->instanceId, (int) $client->getKey());

        $this->assertInstanceOf(ClientMenuiserieDto::class, $result);
        $this->assertSame((int) $client->getKey(), $result->id);
        $this->assertSame('CLI-001', $result->code);
        $this->assertSame('Client Autonome', $result->name);
        $this->assertSame('lead', $result->statut);
        $this->assertSame(0, $result->totalChantiersCount);
        $this->assertSame(0.0, $result->totalRevenueXof);
    }

    public function test_find_maps_menuiserie_attributes(): void
    {
        $client = $this->makeClient('CLI-002', [
            'preferred_contact_method' => 'whatsapp',
            'total_chantiers_count' => 5,
            'total_revenue_xof' => 1250000.50,
            'statut' => 'qualifie',
        ]);

        $result = $this->repository->find($this->instanceId, (int) $client->getKey());

        $this->assertInstanceOf(ClientMenuiserieDto::class, $result);
        $this->assertSame('whatsapp', $result->preferredContactMethod);
        $this->assertSame(5, $result->totalChantiersCount);
        $this->assertSame(1250000.50, $result->totalRevenueXof);
        $this->assertSame('qualifie', $result->statut);
    }

    public function test_can_reference_returns_true_when_active_client_exists(): void
    {
        $client = $this->makeClient('CLI-003');

        $this->assertTrue($this->repository->canReference($this->instanceId, (int) $client->getKey()));
    }

    public function test_can_reference_returns_false_for_unknown_client(): void
    {
        $this->assertFalse($this->repository->canReference($this->instanceId, 99999));
    }

    public function test_can_reference_returns_false_for_inactive_client(): void
    {
        $client = $this->makeClient('CLI-004', ['is_active' => false]);

        $this->assertFalse($this->repository->canReference($this->instanceId, (int) $client->getKey()));
    }

    public function test_can_reference_isolates_by_instance(): void
    {
        $client = $this->makeClient('CLI-005');

        $instanceB = \App\Instances\Instance::create([
            'name' => 'Tenant B',
            'slug' => 'tenant-b-'.uniqid(),
            'is_active' => true,
            'meta' => [],
        ]);

        $this->assertTrue($this->repository->canReference($this->instanceId, (int) $client->getKey()));
        $this->assertFalse($this->repository->canReference($instanceB->id, (int) $client->getKey()));
    }

    public function test_with_menuiserie_history_yields_clients_with_chantiers(): void
    {
        $this->makeClient('CLI-WMH-A', ['total_chantiers_count' => 3, 'total_revenue_xof' => 500000]);
        $this->makeClient('CLI-WMH-B', ['total_chantiers_count' => 0, 'total_revenue_xof' => 0]);

        $results = iterator_to_array($this->repository->withMenuiserieHistory($this->instanceId));

        $this->assertCount(1, $results);
        $this->assertSame('CLI-WMH-A', $results[0]->code);
        $this->assertSame(3, $results[0]->totalChantiersCount);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeClient(string $code, array $overrides = []): ClientMenuiserie
    {
        return ClientMenuiserie::create([
            'instance_id' => $this->instanceId,
            'code' => $code,
            'type' => 'entreprise',
            'nom' => 'Client '.$code,
            'raison_sociale' => 'Client '.$code,
            'email' => null,
            'telephone_principal' => null,
            'ville' => 'Abidjan',
            'pays' => 'CI',
            'statut' => 'lead',
            'is_active' => true,
            ...$overrides,
        ]);
    }
}
