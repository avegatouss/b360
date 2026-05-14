<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Menuiserie360\Domain\Client\Enums\StatutClientMenuiserie;
use Modules\Menuiserie360\Domain\Client\Models\ClientMenuiserie;
use Modules\Menuiserie360\Tests\TestCase;

final class StatutClientWorkflowTest extends TestCase
{
    public function test_client_is_created_with_lead_status_by_default(): void
    {
        $instance = $this->makeRootInstance();
        CurrentInstance::set($instance);

        $client = ClientMenuiserie::create([
            'instance_id' => $instance->id,
            'code' => 'STAT-001',
            'type' => 'particulier',
            'nom' => 'Client Statut',
            'is_active' => true,
        ]);

        $this->assertSame(StatutClientMenuiserie::LEAD, $client->fresh()->getAttribute('statut'));
    }

    public function test_status_can_transition_manually(): void
    {
        $client = $this->makeClient('STAT-002');

        $client->fill(['statut' => StatutClientMenuiserie::QUALIFIE])->save();
        $client->fill(['statut' => StatutClientMenuiserie::CONVERTI])->save();

        $this->assertSame(StatutClientMenuiserie::CONVERTI, $client->fresh()->getAttribute('statut'));
    }

    public function test_clients_can_be_filtered_by_status_per_instance(): void
    {
        $instance = $this->makeRootInstance();
        CurrentInstance::set($instance);

        $qualified = $this->makeClient('STAT-003', $instance->id, StatutClientMenuiserie::QUALIFIE);
        $this->makeClient('STAT-004', $instance->id, StatutClientMenuiserie::LEAD);

        $results = ClientMenuiserie::query()
            ->where('instance_id', $instance->id)
            ->where('statut', StatutClientMenuiserie::QUALIFIE)
            ->pluck('id')
            ->all();

        $this->assertSame([(int) $qualified->getKey()], array_map('intval', $results));
    }

    public function test_status_enum_exposes_badge_helpers(): void
    {
        $this->assertSame('Lead', StatutClientMenuiserie::LEAD->label());
        $this->assertSame('primary', StatutClientMenuiserie::QUALIFIE->color());
        $this->assertSame('danger', StatutClientMenuiserie::CONTENTIEUX->color());
        $this->assertSame('dark', StatutClientMenuiserie::ARCHIVE->color());
    }

    private function makeClient(
        string $code,
        ?int $instanceId = null,
        StatutClientMenuiserie $status = StatutClientMenuiserie::LEAD,
    ): ClientMenuiserie {
        $instanceId ??= $this->makeRootInstance()->id;

        return ClientMenuiserie::create([
            'instance_id' => $instanceId,
            'code' => $code,
            'type' => 'particulier',
            'nom' => 'Client '.$code,
            'statut' => $status,
            'is_active' => true,
        ]);
    }
}
