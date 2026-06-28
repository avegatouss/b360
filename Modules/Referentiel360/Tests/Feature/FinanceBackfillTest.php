<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Tests\Feature;

use Modules\Core\Support\CurrentInstance;
use Modules\Referentiel360\Contracts\Finance\FinanceAttributesDto;
use Modules\Referentiel360\Contracts\Finance\FinanceReader;
use Modules\Referentiel360\Contracts\Finance\FinanceWriter;
use Modules\Referentiel360\Domain\Finance\Models\FinanceDocument;
use Modules\Referentiel360\Domain\Finance\Services\BackfillFinanceService;
use Modules\Referentiel360\Domain\Finance\Services\FinanceMatcher;
use Modules\Referentiel360\Tests\Support\FakeFinanceSource;
use Modules\Referentiel360\Tests\TestCase;

/**
 * ADR-031 / Lot 3 — Backfill / resync : no-op si 0 source, idempotent (full
 * refresh, pas de doublon) + agrégation consolidée (avoir soustrait).
 */
final class FinanceBackfillTest extends TestCase
{
    private int $instanceId;

    protected function setUp(): void
    {
        parent::setUp();
        $root = $this->makeRootInstance();
        $this->instanceId = (int) $root->id;
        CurrentInstance::set($root);
    }

    public function test_backfill_no_op_with_zero_sources(): void
    {
        $service = new BackfillFinanceService([], app(FinanceMatcher::class), app(FinanceWriter::class));

        $report = $service->run([$this->instanceId]);

        $this->assertSame(0, $report->created);
        $this->assertSame(0, $report->matched);
        $this->assertSame(0, $report->linked);
        $this->assertSame(0, FinanceDocument::withoutInstanceScope()->count());
    }

    public function test_backfill_is_idempotent_and_refreshes_on_second_pass(): void
    {
        $source = new FakeFinanceSource('mnu.invoice', [
            $this->instanceId => [
                new FinanceAttributesDto(localId: 1, documentNumber: 'F-1', amountTtc: '120.00', paidAmount: '50.00', sourceModule: 'menuiserie'),
            ],
        ]);

        $service = new BackfillFinanceService([$source], app(FinanceMatcher::class), app(FinanceWriter::class));

        $first = $service->run([$this->instanceId]);
        $this->assertSame(1, $first->created);
        $this->assertSame(0, $first->matched);

        // 2e passage : paiement complété côté source ⇒ match par lien + full refresh.
        $source2 = new FakeFinanceSource('mnu.invoice', [
            $this->instanceId => [
                new FinanceAttributesDto(localId: 1, documentNumber: 'F-1', amountTtc: '120.00', paidAmount: '120.00', sourceModule: 'menuiserie'),
            ],
        ]);
        $service2 = new BackfillFinanceService([$source2], app(FinanceMatcher::class), app(FinanceWriter::class));
        $second = $service2->run([$this->instanceId]);

        $this->assertSame(0, $second->created);
        $this->assertSame(1, $second->matched);
        $this->assertSame(1, FinanceDocument::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());

        $dto = app(FinanceReader::class)->getByLink($this->instanceId, 'mnu.invoice', 1);
        $this->assertNotNull($dto);
        $this->assertSame('120.00', $dto->paidAmount);
        $this->assertSame('paid', $dto->statusNormalized);
    }

    public function test_totals_subtract_credit_notes_and_exclude_cancelled(): void
    {
        $writer = app(FinanceWriter::class);

        $writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
            localId: 1, documentNumber: 'F-1', amountTtc: '1000.00', paidAmount: '400.00', sourceModule: 'menuiserie',
        ));
        $writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
            localId: 2, documentNumber: 'AV-1', docType: 'credit_note', amountTtc: '200.00', paidAmount: '0.00', sourceModule: 'menuiserie',
        ));
        // Document annulé : exclu de l'agrégation.
        $writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
            localId: 3, documentNumber: 'F-X', amountTtc: '999.00', paidAmount: '0.00', isCancelled: true, sourceModule: 'menuiserie',
        ));

        $totals = app(FinanceReader::class)->totalsForInstance($this->instanceId);

        $this->assertSame('800.00', $totals['ttc'], '1000 - 200 (avoir), annulé exclu');
        $this->assertSame('400.00', $totals['paid']);
        $this->assertSame('400.00', $totals['due'], '600 (facture) - 200 (avoir)');
    }
}
