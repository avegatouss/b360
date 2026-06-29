<?php

declare(strict_types=1);

namespace Modules\Eshop360\Tests\Feature\Referentiel;

use App\Instances\Instance;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Finance\Models\Invoice;
use Modules\Eshop360\Integration\Referentiel\EshopFinanceMapper;
use Modules\Eshop360\Integration\Referentiel\EshopInvoiceFinanceSource;
use Modules\Eshop360\Integration\Referentiel\InvoiceFinanceObserver;
use Modules\Eshop360\Tests\TestCase;
use Modules\Referentiel360\Contracts\Finance\FinanceAttributesDto;
use Modules\Referentiel360\Contracts\Finance\FinanceDto;
use Modules\Referentiel360\Contracts\Finance\FinanceWriter;

/**
 * Lot 3.b (ADR-031) — Intégration Eshop360 (invoices) → Referentiel360 (finance).
 *
 * Couvre : push observer post-commit (création document miroir + lien
 * eshop.invoice, montants + statut dérivé), re-push full-refresh sur update de
 * paid_amount, status cancelled, best-effort (FinanceWriter qui throw ne casse
 * pas la création), FinanceSource backfill.
 *
 * Même mécanique RefreshDatabase + afterCommit que les Lots 1.b / 2.b : on
 * installe un transaction manager de test ({@see ImmediateAfterCommitTransactionsManager}).
 */
final class EshopFinanceIntegrationTest extends TestCase
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

    public function test_creating_invoice_pushes_one_finance_document_and_link_with_derived_status(): void
    {
        $invoice = DB::transaction(fn (): Invoice => Invoice::create([
            'instance_id' => $this->instanceId,
            'invoice_number' => 'INV-FIN-001',
            'status' => 'sent',
            'subtotal' => 1000,
            'tax_amount' => 180,
            'discount_amount' => 0,
            'total' => 1180,
            'paid_amount' => 0,
            'due_amount' => 1180,
        ]));

        $this->assertSame(
            1,
            DB::table('ref_documents_finance')->where('instance_id', $this->instanceId)->count()
        );

        $link = DB::table('ref_finance_links')
            ->where('instance_id', $this->instanceId)
            ->where('linkable_type', 'eshop.invoice')
            ->where('linkable_id', $invoice->getKey())
            ->first();
        $this->assertNotNull($link);

        $document = DB::table('ref_documents_finance')->where('instance_id', $this->instanceId)->first();
        $this->assertNotNull($document);
        $this->assertSame('INV-FIN-001', $document->document_number);
        $this->assertSame('invoice', $document->doc_type);
        $this->assertSame('eshop', $document->source_module);
        $this->assertSame(0, bccomp((string) $document->amount_ht, '1000', 2));
        $this->assertSame(0, bccomp((string) $document->amount_tax, '180', 2));
        $this->assertSame(0, bccomp((string) $document->amount_ttc, '1180', 2));
        $this->assertSame(0, bccomp((string) $document->paid_amount, '0', 2));
        $this->assertSame(0, bccomp((string) $document->due_amount, '1180', 2));
        // paid == 0 ⇒ statut dérivé 'issued'.
        $this->assertSame('issued', $document->status_normalized);
    }

    public function test_updating_paid_amount_re_pushes_full_refresh(): void
    {
        $invoice = DB::transaction(fn (): Invoice => Invoice::create([
            'instance_id' => $this->instanceId,
            'invoice_number' => 'INV-FIN-002',
            'status' => 'sent',
            'subtotal' => 1000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total' => 1000,
            'paid_amount' => 0,
            'due_amount' => 1000,
        ]));

        // Paiement partiel : update paid_amount ⇒ re-push full-refresh.
        DB::transaction(function () use ($invoice): void {
            $invoice->update(['paid_amount' => 400, 'due_amount' => 600]);
        });

        $document = DB::table('ref_documents_finance')->where('instance_id', $this->instanceId)->first();
        $this->assertNotNull($document);
        // Toujours un seul document (idempotence sur le lien).
        $this->assertSame(
            1,
            DB::table('ref_documents_finance')->where('instance_id', $this->instanceId)->count()
        );
        $this->assertSame(0, bccomp((string) $document->paid_amount, '400', 2));
        $this->assertSame(0, bccomp((string) $document->due_amount, '600', 2));
        // 0 < paid < ttc ⇒ 'partially_paid'.
        $this->assertSame('partially_paid', $document->status_normalized);
    }

    public function test_cancelled_invoice_yields_cancelled_status(): void
    {
        DB::transaction(fn (): Invoice => Invoice::create([
            'instance_id' => $this->instanceId,
            'invoice_number' => 'INV-FIN-003',
            'status' => 'cancelled',
            'subtotal' => 500,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total' => 500,
            'paid_amount' => 0,
            'due_amount' => 500,
        ]));

        $document = DB::table('ref_documents_finance')->where('instance_id', $this->instanceId)->first();
        $this->assertNotNull($document);
        $this->assertSame(1, (int) $document->is_cancelled);
        $this->assertSame('cancelled', $document->status_normalized);
    }

    public function test_push_is_best_effort_when_finance_writer_throws(): void
    {
        // FinanceWriter qui throw : la création invoice doit réussir, l'exception avalée.
        $this->app->instance(FinanceWriter::class, new class implements FinanceWriter
        {
            public function upsertFromModule(int $instanceId, string $linkType, FinanceAttributesDto $attrs): FinanceDto
            {
                throw new \RuntimeException('Referentiel indisponible');
            }
        });

        // Réattacher l'observer avec le writer mocké (boot a câblé l'ancien).
        Invoice::observe($this->app->make(InvoiceFinanceObserver::class));

        $invoice = DB::transaction(fn (): Invoice => Invoice::create([
            'instance_id' => $this->instanceId,
            'invoice_number' => 'INV-FIN-004',
            'status' => 'sent',
            'subtotal' => 100,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total' => 100,
            'paid_amount' => 0,
            'due_amount' => 100,
        ]));

        // L'invoice existe malgré l'échec du référentiel.
        $this->assertDatabaseHas('eshop_invoices', [
            'id' => $invoice->getKey(),
            'invoice_number' => 'INV-FIN-004',
        ]);
        // Aucun document miroir créé (writer a échoué silencieusement).
        $this->assertSame(
            0,
            DB::table('ref_documents_finance')->where('instance_id', $this->instanceId)->count()
        );
    }

    public function test_invoice_finance_source_yields_expected_dto(): void
    {
        $invoice = Invoice::create([
            'instance_id' => $this->instanceId,
            'invoice_number' => 'INV-FIN-005',
            'status' => 'sent',
            'subtotal' => 2000,
            'tax_amount' => 360,
            'discount_amount' => 0,
            'total' => 2360,
            'paid_amount' => 100,
            'due_amount' => 2260,
        ]);

        $source = new EshopInvoiceFinanceSource(new EshopFinanceMapper);

        $this->assertSame('eshop.invoice', $source->linkType());

        $dtos = iterator_to_array($source->each($this->instanceId));
        $this->assertCount(1, $dtos);

        /** @var FinanceAttributesDto $dto */
        $dto = $dtos[0];
        $this->assertSame((int) $invoice->getKey(), $dto->localId);
        $this->assertSame('INV-FIN-005', $dto->documentNumber);
        $this->assertSame('invoice', $dto->docType);
        $this->assertSame('2000.00', $dto->amountHt);
        $this->assertSame('360.00', $dto->amountTax);
        $this->assertSame('2360.00', $dto->amountTtc);
        $this->assertSame('100.00', $dto->paidAmount);
        $this->assertFalse($dto->isCancelled);
        $this->assertSame('eshop.customer', $dto->partyLinkType);
        $this->assertSame('eshop', $dto->sourceModule);
    }
}
