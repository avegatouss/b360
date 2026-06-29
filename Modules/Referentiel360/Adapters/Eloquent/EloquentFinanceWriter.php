<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Adapters\Eloquent;

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
 *  - SÉRIALISATION SUR LE LIEN (MAJEUR 1) : le point de sérialisation unique est
 *    le `unique(instance_id, linkable_type, linkable_id)` de `ref_finance_links`.
 *    Deux push création concurrents sur une facture neuve : le premier crée
 *    document + lien ; le second lève `UniqueConstraintViolationException` sur le
 *    lien → on RELIT le lien gagnant, on SUPPRIME le document orphelin qu'on
 *    venait de créer (aucun orphelin committé), puis on bascule sur le chemin
 *    update avec `lockForUpdate()`. Idempotence stricte même en concurrence de
 *    création.
 *  - Chemin document existant : `lockForUpdate()` sur la ligne
 *    `ref_documents_finance` AVANT écriture (sérialise les push concurrents de
 *    paiements). La valeur source courante (DTO) est appliquée DANS la transaction.
 *  - FULL REFRESH (last-write-wins) : écrase TOUS les champs miroir (montants,
 *    paid, statut) — PAS de mergeInto non destructif (divergence assumée vs
 *    Tiers/Articles, cf ADR-031 §4). EXCEPTION (MAJEUR 5) : `party_id` ne
 *    DOWNGRADE jamais de non-null vers null (on conserve l'ancien si la
 *    résolution courante échoue).
 *  - `party_id` résolu via PartyReader (lien interne au module, autorisé).
 *  - `due_amount` = max(0, ttc - paid) pour une `invoice` (MAJEUR 4 : jamais
 *    négatif si paid > ttc) ; `status_normalized` DÉRIVÉ (deriveStatus).
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

            // Chemin update : document déjà lié → verrou ligne avant écriture.
            if ($documentId !== null) {
                return $this->refreshExisting($instanceId, $documentId, $linkType, $attrs);
            }

            // Chemin création : on sérialise sur le LIEN (unique constraint). Le
            // document est créé puis immédiatement lié ; si un push concurrent a
            // gagné la course, la création du lien lève et on bascule sur update
            // sans laisser d'orphelin.
            $document = new FinanceDocument;
            $document->instance_id = $instanceId;
            $this->refresh($document, $instanceId, $attrs);

            try {
                FinanceDocumentLink::create([
                    'instance_id' => $instanceId,
                    'document_id' => (int) $document->getKey(),
                    'linkable_type' => $linkType,
                    'linkable_id' => $attrs->localId,
                ]);
            } catch (UniqueConstraintViolationException $e) {
                // Un autre push a créé le lien (et son document) entre notre lecture
                // et notre insertion. On supprime le document qu'on vient de créer
                // (AUCUN orphelin) et on relit le lien gagnant pour basculer update.
                $document->forceDelete();

                $winnerDocumentId = $this->existingDocumentId($instanceId, $linkType, $attrs->localId);
                if ($winnerDocumentId === null) {
                    throw $e; // incohérent : la violation n'a pas de lien gagnant.
                }

                return $this->refreshExisting($instanceId, $winnerDocumentId, $linkType, $attrs);
            }

            FinanceDocumentUpserted::dispatch(
                $instanceId,
                (int) $document->getKey(),
                $linkType,
                $attrs->localId,
                true,
            );

            return EloquentFinanceReader::mapToDto($document->refresh());
        });
    }

    /**
     * Chemin update : verrou ligne (sérialise les push concurrents) + full refresh.
     */
    private function refreshExisting(int $instanceId, int $documentId, string $linkType, FinanceAttributesDto $attrs): FinanceDto
    {
        $document = FinanceDocument::query()
            ->withoutGlobalScope(InstanceScope::class)
            ->where('instance_id', $instanceId)
            ->whereKey($documentId)
            ->lockForUpdate()
            ->firstOrFail();

        $this->refresh($document, $instanceId, $attrs);

        FinanceDocumentUpserted::dispatch(
            $instanceId,
            (int) $document->getKey(),
            $linkType,
            $attrs->localId,
            false,
        );

        return EloquentFinanceReader::mapToDto($document->refresh());
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
     *
     * Deux exceptions au full refresh :
     *  - MAJEUR 4 : pour une `invoice`, `due_amount = max(0, ttc - paid)` (jamais
     *    négatif si paid > ttc). Pour un `credit_note`, on conserve `ttc - paid`
     *    tel quel (montants positifs, sémantique d'avoir cohérente côté reader).
     *  - MAJEUR 5 : `party_id` ne DOWNGRADE jamais de non-null vers null
     *    (cf. resolvePartyId).
     */
    private function refresh(FinanceDocument $document, int $instanceId, FinanceAttributesDto $attrs): void
    {
        $docType = $attrs->docType !== '' ? $attrs->docType : 'invoice';

        $ttc = $this->normalize($attrs->amountTtc);
        $paid = $this->normalize($attrs->paidAmount);
        $due = bcsub($ttc, $paid, self::SCALE);

        // MAJEUR 4 : borne le dû à 0 pour une facture (paid > ttc ⇒ due = 0).
        if ($docType !== 'credit_note' && bccomp($due, '0', self::SCALE) < 0) {
            $due = $this->normalize('0');
        }

        $document->party_id = $this->resolvePartyId($instanceId, $attrs, $document);
        $document->doc_type = $docType;
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

    /**
     * Résout le `party_id` SANS downgrade (MAJEUR 5).
     *
     * Si la résolution courante renvoie un party, on l'applique. Si elle renvoie
     * null mais qu'un `party_id` était déjà lié au document, on CONSERVE l'ancien
     * (log debug) plutôt que de le réécraser à null silencieusement.
     */
    private function resolvePartyId(int $instanceId, FinanceAttributesDto $attrs, FinanceDocument $document): ?int
    {
        $current = $this->intOrNull($document->getAttribute('party_id'));

        if ($attrs->partyLinkType === null || $attrs->partyLocalId === null) {
            return $current;
        }

        $resolved = $this->partyReader->getByLink($instanceId, $attrs->partyLinkType, $attrs->partyLocalId)?->id;

        if ($resolved !== null) {
            return $resolved;
        }

        if ($current !== null) {
            Log::debug('Referentiel360 Finance: party non re-résolu, ancien party_id conservé (pas de downgrade).', [
                'instance_id' => $instanceId,
                'document_id' => $this->intOrNull($document->getKey()),
                'party_link_type' => $attrs->partyLinkType,
                'party_local_id' => $attrs->partyLocalId,
                'kept_party_id' => $current,
            ]);
        }

        return $current;
    }

    private function intOrNull(mixed $value): ?int
    {
        return $value !== null ? (int) $value : null;
    }

    private function normalize(string $value): string
    {
        $value = trim($value);

        return bcadd($value !== '' ? $value : '0', '0', self::SCALE);
    }
}
