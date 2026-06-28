<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Adapters\Eloquent;

use Illuminate\Support\Facades\DB;
use Modules\Core\Database\Scopes\InstanceScope;
use Modules\Referentiel360\Contracts\Finance\FinanceAttributesDto;
use Modules\Referentiel360\Contracts\Finance\FinanceDto;
use Modules\Referentiel360\Contracts\Finance\FinanceWriter;
use Modules\Referentiel360\Contracts\Party\PartyReader;
use Modules\Referentiel360\Domain\Finance\Events\FinanceDocumentUpserted;
use Modules\Referentiel360\Domain\Finance\Models\FinanceDocument;
use Modules\Referentiel360\Domain\Finance\Models\FinanceDocumentLink;

/**
 * Implémentation Eloquent du {@see FinanceWriter} (ADR-031 / Lot 3, ZONE L1).
 *
 * POINTS L1 CRITIQUES :
 *  - `upsertFromModule` en `DB::transaction`.
 *  - Si document existant (matché par lien) : `lockForUpdate()` sur la ligne
 *    `ref_documents_finance` AVANT écriture (sérialise les push concurrents de
 *    paiements). La valeur source courante (DTO) est appliquée DANS la transaction.
 *  - FULL REFRESH (last-write-wins) : écrase TOUS les champs miroir (montants,
 *    paid, statut) — PAS de mergeInto non destructif (divergence assumée vs
 *    Tiers/Articles, cf ADR-031 §4).
 *  - `party_id` résolu via PartyReader (lien interne au module, autorisé) : null
 *    si non résolu.
 *  - `due_amount` = ttc - paid ; `status_normalized` DÉRIVÉ (deriveStatus).
 *    Comparaisons décimales en bcmath (échelle 2) — aucun float intermédiaire.
 *  - `ensureLink` idempotent ; dispatch FinanceDocumentUpserted.
 *
 * INVARIANT ABSOLU : aucune écriture vers `mnu_*` / `eshop_*`.
 */
final class EloquentFinanceWriter implements FinanceWriter
{
    private const SCALE = 2;

    public function __construct(private readonly PartyReader $partyReader) {}

    public function upsertFromModule(int $instanceId, string $linkType, FinanceAttributesDto $attrs): FinanceDto
    {
        return DB::transaction(function () use ($instanceId, $linkType, $attrs): FinanceDto {
            $documentId = $this->existingDocumentId($instanceId, $linkType, $attrs->localId);

            $created = false;

            if ($documentId === null) {
                $document = new FinanceDocument;
                $document->instance_id = $instanceId;
                $created = true;
            } else {
                // L1 : verrou ligne avant écriture — sérialise les push concurrents.
                $document = FinanceDocument::query()
                    ->withoutGlobalScope(InstanceScope::class)
                    ->where('instance_id', $instanceId)
                    ->whereKey($documentId)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $this->refresh($document, $instanceId, $attrs);

            $this->ensureLink($instanceId, (int) $document->getKey(), $linkType, $attrs->localId);

            FinanceDocumentUpserted::dispatch(
                $instanceId,
                (int) $document->getKey(),
                $linkType,
                $attrs->localId,
                $created,
            );

            return EloquentFinanceReader::mapToDto($document->refresh());
        });
    }

    private function existingDocumentId(int $instanceId, string $linkType, int $localId): ?int
    {
        $link = FinanceDocumentLink::withoutInstanceScope()
            ->where('instance_id', $instanceId)
            ->where('linkable_type', $linkType)
            ->where('linkable_id', $localId)
            ->first();

        return $link !== null ? (int) $link->getAttribute('document_id') : null;
    }

    /**
     * FULL REFRESH : écrase tous les champs miroir avec la valeur source courante.
     */
    private function refresh(FinanceDocument $document, int $instanceId, FinanceAttributesDto $attrs): void
    {
        $ttc = $this->normalize($attrs->amountTtc);
        $paid = $this->normalize($attrs->paidAmount);
        $due = bcsub($ttc, $paid, self::SCALE);

        $document->party_id = $this->resolvePartyId($instanceId, $attrs);
        $document->doc_type = $attrs->docType !== '' ? $attrs->docType : 'invoice';
        $document->document_number = $attrs->documentNumber;
        $document->currency = $attrs->currency !== '' ? $attrs->currency : 'XOF';
        $document->amount_ht = $this->normalize($attrs->amountHt);
        $document->amount_tax = $this->normalize($attrs->amountTax);
        $document->amount_ttc = $ttc;
        $document->paid_amount = $paid;
        $document->due_amount = $due;
        $document->status_normalized = $this->deriveStatus($attrs->isCancelled, $paid, $ttc);
        $document->is_cancelled = $attrs->isCancelled;
        $document->issued_at = $attrs->issuedAt;
        $document->due_date = $attrs->dueDate;
        $document->source_module = $attrs->sourceModule;
        $document->save();
    }

    /**
     * Statut DÉRIVÉ des montants (ADR-031 §3). Comparaisons bcmath, échelle 2 :
     *  - cancelled si isCancelled ;
     *  - sinon paid == 0           → issued ;
     *  - sinon 0 < paid < ttc      → partially_paid ;
     *  - sinon paid >= ttc         → paid.
     */
    private function deriveStatus(bool $isCancelled, string $paid, string $ttc): string
    {
        if ($isCancelled) {
            return 'cancelled';
        }

        if (bccomp($paid, '0', self::SCALE) === 0) {
            return 'issued';
        }

        if (bccomp($paid, $ttc, self::SCALE) >= 0) {
            return 'paid';
        }

        return 'partially_paid';
    }

    private function resolvePartyId(int $instanceId, FinanceAttributesDto $attrs): ?int
    {
        if ($attrs->partyLinkType === null || $attrs->partyLocalId === null) {
            return null;
        }

        $party = $this->partyReader->getByLink($instanceId, $attrs->partyLinkType, $attrs->partyLocalId);

        return $party?->id;
    }

    private function ensureLink(int $instanceId, int $documentId, string $linkType, int $localId): void
    {
        $exists = FinanceDocumentLink::withoutInstanceScope()
            ->where('instance_id', $instanceId)
            ->where('linkable_type', $linkType)
            ->where('linkable_id', $localId)
            ->exists();

        if ($exists) {
            return;
        }

        FinanceDocumentLink::create([
            'instance_id' => $instanceId,
            'document_id' => $documentId,
            'linkable_type' => $linkType,
            'linkable_id' => $localId,
        ]);
    }

    private function normalize(string $value): string
    {
        $value = trim($value);

        return bcadd($value !== '' ? $value : '0', '0', self::SCALE);
    }
}
