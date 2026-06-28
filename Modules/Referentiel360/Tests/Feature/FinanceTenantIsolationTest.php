<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Tests\Feature;

use App\Instances\Instance;
use Modules\Core\Support\CurrentInstance;
use Modules\Referentiel360\Contracts\Finance\FinanceAttributesDto;
use Modules\Referentiel360\Contracts\Finance\FinanceReader;
use Modules\Referentiel360\Contracts\Finance\FinanceWriter;
use Modules\Referentiel360\Domain\Finance\Models\FinanceDocument;
use Modules\Referentiel360\Tests\TestCase;

/**
 * ADR-031 / Lot 3 — Isolation multi-tenant : même document_number + même
 * linkType + même localId dans 2 instances ⇒ 2 documents distincts, montants
 * isolés.
 */
final class FinanceTenantIsolationTest extends TestCase
{
    public function test_same_document_number_in_two_instances_yields_two_distinct_documents(): void
    {
        $a = $this->makeRootInstance();
        $b = Instance::create([
            'name' => 'Tenant B',
            'slug' => 'tenant-b-'.uniqid(),
            'is_active' => true,
            'meta' => [],
        ]);

        $writer = app(FinanceWriter::class);
        $reader = app(FinanceReader::class);

        CurrentInstance::set($a);
        $da = $writer->upsertFromModule((int) $a->id, 'mnu.invoice', new FinanceAttributesDto(
            localId: 1,
            documentNumber: 'SHARED-001',
            amountTtc: '100.00',
            paidAmount: '100.00',
            sourceModule: 'menuiserie',
        ));

        CurrentInstance::set($b);
        $db = $writer->upsertFromModule((int) $b->id, 'mnu.invoice', new FinanceAttributesDto(
            localId: 1,
            documentNumber: 'SHARED-001',
            amountTtc: '300.00',
            paidAmount: '0.00',
            sourceModule: 'menuiserie',
        ));

        $this->assertNotSame($da->id, $db->id);
        $this->assertSame(1, FinanceDocument::withoutInstanceScope()->where('instance_id', $a->id)->count());
        $this->assertSame(1, FinanceDocument::withoutInstanceScope()->where('instance_id', $b->id)->count());
        $this->assertSame(2, FinanceDocument::withoutInstanceScope()->count());

        // Montants isolés par instance.
        $this->assertSame('100.00', $reader->totalsForInstance((int) $a->id)['ttc']);
        $this->assertSame('300.00', $reader->totalsForInstance((int) $b->id)['ttc']);
    }
}
