<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Tests\Feature;

use App\Instances\Instance;
use Modules\Core\Support\CurrentInstance;
use Modules\Referentiel360\Contracts\Party\PartyAttributesDto;
use Modules\Referentiel360\Contracts\Party\PartyWriter;
use Modules\Referentiel360\Domain\Party\Models\Party;
use Modules\Referentiel360\Tests\TestCase;

/**
 * ADR-030 — Aucune fusion cross-instance : même email dans 2 instances ⇒ 2 parties.
 */
final class PartyTenantIsolationTest extends TestCase
{
    public function test_same_email_in_two_instances_yields_two_distinct_parties(): void
    {
        $a = $this->makeRootInstance();
        $b = Instance::create([
            'name' => 'Tenant B',
            'slug' => 'tenant-b-'.uniqid(),
            'is_active' => true,
            'meta' => [],
        ]);

        $writer = app(PartyWriter::class);
        $email = 'shared@cross.ci';

        CurrentInstance::set($a);
        $pa = $writer->upsertFromModule((int) $a->id, 'mnu.client', new PartyAttributesDto(
            localId: 1, isCustomer: true, displayName: 'A', email: $email,
        ));

        CurrentInstance::set($b);
        $pb = $writer->upsertFromModule((int) $b->id, 'mnu.client', new PartyAttributesDto(
            localId: 1, isCustomer: true, displayName: 'B', email: $email,
        ));

        $this->assertNotSame($pa->id, $pb->id);
        $this->assertSame(1, Party::withoutInstanceScope()->where('instance_id', $a->id)->count());
        $this->assertSame(1, Party::withoutInstanceScope()->where('instance_id', $b->id)->count());
        $this->assertSame(2, Party::withoutInstanceScope()->count());
    }
}
