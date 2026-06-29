<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Feature\Referentiel;

use App\Instances\Instance;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Core\Support\TeamContext;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;
use Modules\Menuiserie360\Integration\Referentiel\InvoiceFinanceObserver;
use Modules\Menuiserie360\Integration\Referentiel\MenuiserieFinanceMapper;
use Modules\Menuiserie360\Integration\Referentiel\MenuiserieInvoiceFinanceSource;
use Modules\Menuiserie360\Tests\TestCase;
use Modules\Referentiel360\Contracts\Finance\FinanceAttributesDto;
use Modules\Referentiel360\Contracts\Finance\FinanceDto;
use Modules\Referentiel360\Contracts\Finance\FinanceWriter;

/**
 * Lot 3.a (ADR-031, ZONE L1) — Intégration finance Menuiserie360 → Referentiel360.
 *
 * Couvre : push observer post-commit (création facture ⇒ 1 document miroir +
 * lien `mnu.invoice`, statut dérivé), re-push sur encaissement (full-refresh
 * paid/due/status), concurrence séquentielle (last-write-wins), avoir
 * (docType='credit_note'), best-effort (Writer qui throw ne casse pas la
 * facture) et FinanceSource backfill.
 *
 * Note RefreshDatabase + afterCommit : on installe le transaction manager de
 * test {@see ImmediateAfterCommitTransactionsManager} (cf. Lot 1.a) qui exécute
 * les callbacks afterCommit au commit de la transaction métier imbriquée
 * (niveau 1).
 */
final class MenuiserieFinanceIntegrationTest extends TestCase
{
    private Instance $instance;

    private int $instanceId;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $this->instance = $this->makeRootInstance();
        $this->instanceId = (int) $this->instance->id;
        CurrentInstance::set($this->instance);
        TeamContext::set(0);

        // Force l'exécution des afterCommit au commit vers le niveau 1 (cf. classe).
        $manager = new ImmediateAfterCommitTransactionsManager;
        foreach (['sqlite', 'system'] as $name) {
            DB::connection($name)->setTransactionManager($manager);
        }
        $this->app->instance('db.transactions', $manager);
    }

    public function test_creating_invoice_pushes_one_mirror_document_and_link(): void
    {
        $invoice = DB::transaction(fn (): MenuiserieInvoice => MenuiserieInvoice::create([
            'instance_id' => $this->instanceId,
            'invoice_number' => 'MNU-FAC-2026-0001',
            'client_id' => 42,
            'type' => 'solde',
            'amount_ht' => 100000,
            'tax_rate' => 0.18,
            'amount_tva' => 18000,
            'amount_ttc' => 118000,
            'paid_amount' => 50000,
            'status' => 'issued',
            'issued_at' => '2026-06-28 10:00:00',
        ]));

        $this->assertSame(1, DB::table('ref_documents_finance')->where('instance_id', $this->instanceId)->count());

        $document = DB::table('ref_documents_finance')->where('instance_id', $this->instanceId)->first();
        $this->assertNotNull($document);
        $this->assertSame('MNU-FAC-2026-0001', $document->document_number);
        $this->assertSame('invoice', $document->doc_type);
        $this->assertSame('XOF', $document->currency);
        // DB::table renvoie la valeur brute SGBD (pas de cast décimal Eloquent).
        $this->assertEqualsWithDelta(118000.0, (float) $document->amount_ttc, 0.001);
        $this->assertEqualsWithDelta(50000.0, (float) $document->paid_amount, 0.001);
        $this->assertEqualsWithDelta(68000.0, (float) $document->due_amount, 0.001, 'due = ttc - paid');
        // paid partiel ⇒ statut dérivé partially_paid.
        $this->assertSame('partially_paid', $document->status_normalized);
        $this->assertSame('menuiserie', $document->source_module);

        $link = DB::table('ref_finance_links')
            ->where('instance_id', $this->instanceId)
            ->where('linkable_type', 'mnu.invoice')
            ->where('linkable_id', $invoice->getKey())
            ->first();

        $this->assertNotNull($link);
        $this->assertSame((int) $document->id, (int) $link->document_id);
    }

    public function test_updating_paid_amount_repushes_full_refresh_mirror(): void
    {
        $invoice = DB::transaction(fn (): MenuiserieInvoice => MenuiserieInvoice::create([
            'instance_id' => $this->instanceId,
            'invoice_number' => 'MNU-FAC-2026-0002',
            'client_id' => 42,
            'type' => 'solde',
            'amount_ht' => 100000,
            'tax_rate' => 0.18,
            'amount_tva' => 18000,
            'amount_ttc' => 118000,
            'paid_amount' => 0,
            'status' => 'issued',
            'issued_at' => '2026-06-28 10:00:00',
        ]));

        $document = DB::table('ref_documents_finance')->where('instance_id', $this->instanceId)->first();
        $this->assertNotNull($document);
        $this->assertEqualsWithDelta(0.0, (float) $document->paid_amount, 0.001);
        $this->assertEqualsWithDelta(118000.0, (float) $document->due_amount, 0.001);
        $this->assertSame('issued', $document->status_normalized);

        // Simule un encaissement total (RecordPaymentAction met à jour paid_amount + status).
        DB::transaction(function () use ($invoice): void {
            $invoice->update([
                'paid_amount' => 118000,
                'status' => 'paid_full',
            ]);
        });

        // Full refresh : un seul document miroir, montants/statut mis à jour.
        $this->assertSame(1, DB::table('ref_documents_finance')->where('instance_id', $this->instanceId)->count());

        $refreshed = DB::table('ref_documents_finance')->where('instance_id', $this->instanceId)->first();
        $this->assertNotNull($refreshed);
        $this->assertSame((int) $document->id, (int) $refreshed->id, 'même document miroir (pas de doublon)');
        $this->assertEqualsWithDelta(118000.0, (float) $refreshed->paid_amount, 0.001);
        $this->assertEqualsWithDelta(0.0, (float) $refreshed->due_amount, 0.001);
        $this->assertSame('paid', $refreshed->status_normalized);
    }

    /**
     * Concurrence (L1, séquentielle sous SQLite) : 2 encaissements croissants
     * (50 000 puis 120 000 sur ttc=120 000) ⇒ état final paid=120 000, status
     * `paid`. La vraie sérialisation inter-requêtes est garantie côté module par
     * le `lockForUpdate` sur la facture (RecordPaymentAction) et côté socle par
     * le `lockForUpdate` du Writer sur la ligne ref_documents_finance existante.
     */
    public function test_concurrent_payment_pushes_converge_to_last_write(): void
    {
        $invoice = DB::transaction(fn (): MenuiserieInvoice => MenuiserieInvoice::create([
            'instance_id' => $this->instanceId,
            'invoice_number' => 'MNU-FAC-2026-0003',
            'client_id' => 42,
            'type' => 'solde',
            'amount_ht' => 101695,
            'tax_rate' => 0.18,
            'amount_tva' => 18305,
            'amount_ttc' => 120000,
            'paid_amount' => 0,
            'status' => 'issued',
            'issued_at' => '2026-06-28 10:00:00',
        ]));

        DB::transaction(fn () => $invoice->update([
            'paid_amount' => 50000,
            'status' => 'paid_partial',
        ]));

        DB::transaction(fn () => $invoice->update([
            'paid_amount' => 120000,
            'status' => 'paid_full',
        ]));

        $document = DB::table('ref_documents_finance')->where('instance_id', $this->instanceId)->first();
        $this->assertNotNull($document);
        $this->assertEqualsWithDelta(120000.0, (float) $document->paid_amount, 0.001);
        $this->assertEqualsWithDelta(0.0, (float) $document->due_amount, 0.001);
        $this->assertSame('paid', $document->status_normalized);
        $this->assertSame(1, DB::table('ref_documents_finance')->where('instance_id', $this->instanceId)->count());
    }

    public function test_credit_note_invoice_maps_to_credit_note_doc_type(): void
    {
        DB::transaction(fn (): MenuiserieInvoice => MenuiserieInvoice::create([
            'instance_id' => $this->instanceId,
            'invoice_number' => 'MNU-AV-2026-0001',
            'client_id' => 42,
            'type' => 'avoir',
            'amount_ht' => 10000,
            'tax_rate' => 0.18,
            'amount_tva' => 1800,
            'amount_ttc' => 11800,
            'paid_amount' => 0,
            'status' => 'issued',
            'issued_at' => '2026-06-28 10:00:00',
        ]));

        $document = DB::table('ref_documents_finance')->where('instance_id', $this->instanceId)->first();
        $this->assertNotNull($document);
        $this->assertSame('credit_note', $document->doc_type);
        $this->assertEqualsWithDelta(11800.0, (float) $document->amount_ttc, 0.001, 'montants stockés positifs');
    }

    public function test_push_is_best_effort_when_finance_writer_throws(): void
    {
        // FinanceWriter qui throw : la création de la facture doit réussir, l'exception avalée.
        $this->app->instance(FinanceWriter::class, new class implements FinanceWriter
        {
            public function upsertFromModule(int $instanceId, string $linkType, FinanceAttributesDto $attrs): FinanceDto
            {
                throw new \RuntimeException('Referentiel indisponible');
            }
        });

        // Réattacher l'observer avec le writer mocké (boot a câblé l'ancien).
        MenuiserieInvoice::observe($this->app->make(InvoiceFinanceObserver::class));

        $invoice = DB::transaction(fn (): MenuiserieInvoice => MenuiserieInvoice::create([
            'instance_id' => $this->instanceId,
            'invoice_number' => 'MNU-FAC-2026-0099',
            'client_id' => 42,
            'type' => 'solde',
            'amount_ht' => 100000,
            'tax_rate' => 0.18,
            'amount_tva' => 18000,
            'amount_ttc' => 118000,
            'paid_amount' => 0,
            'status' => 'issued',
            'issued_at' => '2026-06-28 10:00:00',
        ]));

        // La facture existe malgré l'échec du référentiel.
        $this->assertDatabaseHas('mnu_invoices', [
            'id' => $invoice->getKey(),
            'invoice_number' => 'MNU-FAC-2026-0099',
        ]);
        // Aucun document miroir créé (writer a échoué silencieusement).
        $this->assertSame(0, DB::table('ref_documents_finance')->where('instance_id', $this->instanceId)->count());
    }

    public function test_invoice_finance_source_yields_expected_dto(): void
    {
        MenuiserieInvoice::create([
            'instance_id' => $this->instanceId,
            'invoice_number' => 'MNU-FAC-2026-0500',
            'client_id' => 77,
            'type' => 'acompte',
            'amount_ht' => 50000,
            'tax_rate' => 0.18,
            'amount_tva' => 9000,
            'amount_ttc' => 59000,
            'paid_amount' => 59000,
            'status' => 'paid_full',
            'issued_at' => '2026-06-28 10:00:00',
        ]);

        $source = new MenuiserieInvoiceFinanceSource(new MenuiserieFinanceMapper);

        $this->assertSame('mnu.invoice', $source->linkType());

        $dtos = iterator_to_array($source->each($this->instanceId));
        $this->assertCount(1, $dtos);

        /** @var FinanceAttributesDto $dto */
        $dto = $dtos[0];
        $this->assertSame('MNU-FAC-2026-0500', $dto->documentNumber);
        $this->assertSame('invoice', $dto->docType);
        $this->assertSame('XOF', $dto->currency);
        $this->assertSame('50000.00', $dto->amountHt);
        $this->assertSame('9000.00', $dto->amountTax);
        $this->assertSame('59000.00', $dto->amountTtc);
        $this->assertSame('59000.00', $dto->paidAmount);
        $this->assertFalse($dto->isCancelled);
        $this->assertSame('mnu.client', $dto->partyLinkType);
        $this->assertSame(77, $dto->partyLocalId);
        $this->assertNull($dto->dueDate);
        $this->assertSame('menuiserie', $dto->sourceModule);
    }
}
