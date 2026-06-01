<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Menuiserie360\Domain\Client\Contracts\ClientRepositoryContract;
use Modules\Menuiserie360\Domain\Client\Models\ClientMenuiserie;
use Modules\Menuiserie360\Tests\TestCase;

final class ClientMenuiserieAutonomousTest extends TestCase
{
    public function test_repository_reads_native_client_without_eshop360_binding(): void
    {
        $instance = $this->makeRootInstance();
        CurrentInstance::set($instance);

        $this->app->forgetInstance(ClientRepositoryContract::class);

        $client = ClientMenuiserie::create([
            'instance_id' => $instance->id,
            'code' => 'MNU-CLI-001',
            'type' => 'entreprise',
            'nom' => 'Dupont',
            'raison_sociale' => 'Dupont SARL',
            'email' => 'contact@dupont.test',
            'telephone_principal' => '+2250700000000',
            'ville' => 'Abidjan',
            'statut' => 'lead',
            'is_active' => true,
        ]);

        $result = app(ClientRepositoryContract::class)->find($instance->id, (int) $client->getKey());

        $this->assertNotNull($result);
        $this->assertSame('MNU-CLI-001', $result->code);
        $this->assertSame('Dupont SARL', $result->name);
        $this->assertSame('lead', $result->statut);
    }
}
