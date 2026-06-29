<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Modules\Core\Support\CurrentInstance;
use Modules\Referentiel360\Contracts\Finance\FinanceAttributesDto;
use Modules\Referentiel360\Contracts\Finance\FinanceWriter;
use Modules\Referentiel360\Domain\Finance\Events\FinanceDocumentUpserted;
use Modules\Referentiel360\Domain\Finance\Models\FinanceDocument;
use Modules\Referentiel360\Domain\Finance\Models\FinanceDocumentLink;
use Modules\Referentiel360\Tests\TestCase;

/**
 * ADR-031 / Lot 3 (ZONE L1) — Nominal + statut dérivé + full refresh / idempotence
 * + concurrence simulée + avoir.
 */
final class FinanceWriterUpsertTest extends TestCase
{
    private int $instanceId;

    private FinanceWriter $writer;

    protected function setUp(): void
    {
        parent::setUp();
        $root = $this->makeRootInstance();
        $this->instanceId = (int) $root->id;
        CurrentInstance::set($root);
        $this->writer = app(FinanceWriter::class);
    }

    public function test_nominal_upsert_creates_mirror_document_link_and_event(): void
    {
        Event::fake([FinanceDocumentUpserted::class]);

        $dto = $this->writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
            localId: 501,
            documentNumber: 'MNU-FAC-2026-0001',
            docType: 'invoice',
            currency: 'XOF',
            amountHt: '100000.00',
            amountTax: '18000.00',
            amountTtc: '118000.00',
            paidAmount: '50000.00',
            issuedAt: '2026-06-28 10:00:00',
            dueDate: '2026-07-28',
            sourceModule: 'menuiserie',
        ));

        // Golden mince + dérivés.
        $this->assertSame('MNU-FAC-2026-0001', $dto->documentNumber);
        $this->assertSame('118000.00', $dto->amountTtc);
        $this->assertSame('50000.00', $dto->paidAmount);
        $this->assertSame('68000.00', $dto->dueAmount, 'due_amount = ttc - paid');
        $this->assertSame('partially_paid', $dto->statusNormalized);
        $this->assertFalse($dto->isCancelled);
        $this->assertNotEmpty($dto->documentUid);

        $this->assertSame(1, FinanceDocument::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());

        $link = FinanceDocumentLink::withoutInstanceScope()
            ->where('instance_id', $this->instanceId)
            ->where('linkable_type', 'mnu.invoice')
            ->where('linkable_id', 501)
            ->first();
        $this->assertNotNull($link);
        $this->assertSame($dto->id, (int) $link->getAttribute('document_id'));

        Event::assertDispatched(FinanceDocumentUpserted::class, fn (FinanceDocumentUpserted $e): bool => $e->created === true && $e->localId === 501 && $e->linkType === 'mnu.invoice');
    }

    /**
     * @return array<string, array{paid: string, ttc: string, cancelled: bool, expected: string}>
     */
    public static function statusProvider(): array
    {
        return [
            'paid=0 => issued' => ['paid' => '0.00', 'ttc' => '120.00', 'cancelled' => false, 'expected' => 'issued'],
            'partial => partially_paid' => ['paid' => '50.00', 'ttc' => '120.00', 'cancelled' => false, 'expected' => 'partially_paid'],
            'settled => paid' => ['paid' => '120.00', 'ttc' => '120.00', 'cancelled' => false, 'expected' => 'paid'],
            'cancelled overrides' => ['paid' => '50.00', 'ttc' => '120.00', 'cancelled' => true, 'expected' => 'cancelled'],
        ];
    }

    /**
     * @dataProvider statusProvider
     */
    public function test_status_normalized_is_derived(string $paid, string $ttc, bool $cancelled, string $expected): void
    {
        $dto = $this->writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
            localId: 1,
            documentNumber: 'F-1',
            amountTtc: $ttc,
            paidAmount: $paid,
            isCancelled: $cancelled,
            sourceModule: 'menuiserie',
        ));

        $this->assertSame($expected, $dto->statusNormalized);
    }

    public function test_full_refresh_idempotent_no_duplicate(): void
    {
        $a = $this->writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
            localId: 7,
            documentNumber: 'F-7',
            amountTtc: '120.00',
            paidAmount: '40.00',
            sourceModule: 'menuiserie',
        ));

        $this->assertSame('partially_paid', $a->statusNormalized);
        $this->assertSame('80.00', $a->dueAmount);

        // Re-push même lien avec paidAmount plus élevé ⇒ miroir mis à jour, pas de doublon.
        $b = $this->writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
            localId: 7,
            documentNumber: 'F-7',
            amountTtc: '120.00',
            paidAmount: '120.00',
            sourceModule: 'menuiserie',
        ));

        $this->assertSame($a->id, $b->id);
        $this->assertSame('120.00', $b->paidAmount, 'full refresh : paid écrasé');
        $this->assertSame('0.00', $b->dueAmount);
        $this->assertSame('paid', $b->statusNormalized);

        $this->assertSame(1, FinanceDocument::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
        $this->assertSame(1, FinanceDocumentLink::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
    }

    /**
     * Concurrence (L1) : 2 push successifs pour le même document avec paidAmount
     * croissant (50 puis 120 sur ttc=120) ⇒ état final paid=120, status `paid`.
     *
     * NB : la sérialisation réelle inter-requêtes est garantie côté module par le
     * `lockForUpdate` sur la facture (hors socle) ; ici on vérifie la monotonie /
     * last-write-wins du miroir + l'absence de doublon (lockForUpdate présent dans
     * le Writer sur la ligne ref_documents_finance existante).
     */
    public function test_concurrent_payment_pushes_converge_to_last_write(): void
    {
        $this->writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
            localId: 9,
            documentNumber: 'F-9',
            amountTtc: '120.00',
            paidAmount: '50.00',
            sourceModule: 'menuiserie',
        ));

        $final = $this->writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
            localId: 9,
            documentNumber: 'F-9',
            amountTtc: '120.00',
            paidAmount: '120.00',
            sourceModule: 'menuiserie',
        ));

        $this->assertSame('120.00', $final->paidAmount);
        $this->assertSame('0.00', $final->dueAmount);
        $this->assertSame('paid', $final->statusNormalized);
        $this->assertSame(1, FinanceDocument::withoutInstanceScope()->where('instance_id', $this->instanceId)->count());
    }

    /**
     * MAJEUR 4 — paid > ttc : due bornée à 0 (jamais négatif), statut `paid`.
     */
    public function test_overpaid_invoice_due_is_floored_to_zero(): void
    {
        $dto = $this->writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
            localId: 21,
            documentNumber: 'F-21',
            amountTtc: '100.00',
            paidAmount: '150.00', // trop-perçu
            sourceModule: 'menuiserie',
        ));

        $this->assertSame('0.00', $dto->dueAmount, 'due bornée à 0 si paid > ttc (MAJEUR 4)');
        $this->assertSame('paid', $dto->statusNormalized);
    }

    /**
     * MAJEUR 4 — ttc = 0 et paid = 0 : due = 0, statut cohérent `issued`.
     */
    public function test_zero_ttc_invoice_is_consistent(): void
    {
        $dto = $this->writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
            localId: 22,
            documentNumber: 'F-22',
            amountTtc: '0.00',
            paidAmount: '0.00',
            sourceModule: 'menuiserie',
        ));

        $this->assertSame('0.00', $dto->dueAmount);
        $this->assertSame('issued', $dto->statusNormalized, 'paid=0 ⇒ issued même si ttc=0');
    }

    /**
     * MAJEUR 1 — deux upserts CRÉATION concurrents (séquentiels en SQLite) sur le
     * même lien produisent UN SEUL document et UN SEUL lien (le 2e doit basculer
     * sur le chemin update via la contrainte d'unicité du lien, pas créer d'orphelin).
     */
    public function test_concurrent_creation_yields_single_document_and_link(): void
    {
        $a = $this->writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
            localId: 33,
            documentNumber: 'F-33',
            amountTtc: '100.00',
            paidAmount: '0.00',
            sourceModule: 'menuiserie',
        ));

        $b = $this->writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
            localId: 33,
            documentNumber: 'F-33',
            amountTtc: '100.00',
            paidAmount: '100.00',
            sourceModule: 'menuiserie',
        ));

        $this->assertSame($a->id, $b->id);
        $this->assertSame(1, FinanceDocument::withoutInstanceScope()
            ->where('instance_id', $this->instanceId)->where('document_number', 'F-33')->count());
        $this->assertSame(1, FinanceDocumentLink::withoutInstanceScope()
            ->where('instance_id', $this->instanceId)
            ->where('linkable_type', 'mnu.invoice')
            ->where('linkable_id', 33)->count());
    }

    /**
     * MAJEUR 1 (catch path déterministe) — simule un push concurrent qui GAGNE la
     * course pile dans la fenêtre TOCTOU : un hook `created` insère le lien gagnant
     * (+ document) juste après que NOTRE document a été persisté mais avant notre
     * `FinanceDocumentLink::create`. Notre insert de lien lève alors
     * UniqueConstraintViolationException → on doit supprimer notre document orphelin
     * et basculer sur le document gagnant. Résultat : 1 lien, 0 orphelin.
     */
    public function test_creation_race_catch_removes_orphan_and_switches_to_winner(): void
    {
        $winnerDocumentId = null;
        $fired = false;

        $hook = function (FinanceDocument $doc) use (&$winnerDocumentId, &$fired): void {
            // One-shot : ne réagit qu'à NOTRE document (F-44), pas au gagnant inséré ici.
            if ($fired || $doc->getAttribute('document_number') !== 'F-44') {
                return;
            }
            $fired = true;

            $winner = FinanceDocument::withoutInstanceScope()->create([
                'instance_id' => $this->instanceId,
                'document_uid' => (string) Str::ulid(),
                'doc_type' => 'invoice',
                'document_number' => 'F-44-WINNER',
                'currency' => 'XOF',
                'amount_ht' => '0',
                'amount_tax' => '0',
                'amount_ttc' => '100.00',
                'paid_amount' => '0.00',
                'due_amount' => '100.00',
                'status_normalized' => 'issued',
                'is_cancelled' => false,
                'source_module' => 'menuiserie',
            ]);
            $winnerDocumentId = (int) $winner->getKey();

            FinanceDocumentLink::withoutInstanceScope()->create([
                'instance_id' => $this->instanceId,
                'document_id' => $winnerDocumentId,
                'linkable_type' => 'mnu.invoice',
                'linkable_id' => 44,
            ]);
        };

        FinanceDocument::created($hook);

        try {
            $dto = $this->writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
                localId: 44,
                documentNumber: 'F-44',
                amountTtc: '100.00',
                paidAmount: '100.00',
                sourceModule: 'menuiserie',
            ));
        } finally {
            FinanceDocument::flushEventListeners();
        }

        // Le dto retourné est le document GAGNANT (mis à jour via le chemin update,
        // full refresh ⇒ il porte désormais document_number 'F-44' de la source).
        $this->assertSame($winnerDocumentId, $dto->id);
        $this->assertSame('100.00', $dto->paidAmount, 'full refresh appliqué au gagnant');
        $this->assertSame('F-44', $dto->documentNumber, 'full refresh : document_number source appliqué au gagnant');

        // 0 orphelin : un seul lien et UN SEUL document subsistent pour ce localId
        // (notre document créé puis forceDelete ; seul le gagnant reste).
        $this->assertSame(1, FinanceDocumentLink::withoutInstanceScope()
            ->where('instance_id', $this->instanceId)
            ->where('linkable_type', 'mnu.invoice')
            ->where('linkable_id', 44)->count());

        // Lecture DB brute (hors soft-delete/scope) : exactement 1 document subsiste,
        // c'est le gagnant. Notre document orphelin a bien été forceDelete.
        $rows = \Illuminate\Support\Facades\DB::table('ref_documents_finance')
            ->where('instance_id', $this->instanceId)
            ->pluck('id');
        $this->assertCount(1, $rows, 'aucun document orphelin (forceDelete) — MAJEUR 1');
        $this->assertSame($winnerDocumentId, (int) $rows->first(), 'le seul document restant est le gagnant');
    }

    public function test_credit_note_stored_with_positive_amounts(): void
    {
        $dto = $this->writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
            localId: 12,
            documentNumber: 'MNU-AV-2026-0003',
            docType: 'credit_note',
            amountHt: '10000.00',
            amountTax: '1800.00',
            amountTtc: '11800.00',
            paidAmount: '0.00',
            sourceModule: 'menuiserie',
        ));

        $this->assertSame('credit_note', $dto->docType);
        $this->assertSame('11800.00', $dto->amountTtc, 'montants stockés positifs');
        $this->assertSame('issued', $dto->statusNormalized);
    }
}
