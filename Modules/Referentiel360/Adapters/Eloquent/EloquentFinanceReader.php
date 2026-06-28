<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Adapters\Eloquent;

use Modules\Core\Database\Scopes\InstanceScope;
use Modules\Referentiel360\Contracts\Finance\FinanceDto;
use Modules\Referentiel360\Contracts\Finance\FinanceReader;
use Modules\Referentiel360\Domain\Finance\Models\FinanceDocument;
use Modules\Referentiel360\Domain\Finance\Models\FinanceDocumentLink;

/**
 * Implémentation Eloquent du {@see FinanceReader} (ADR-031 / Lot 3).
 *
 * Unique endroit (avec le Writer) où les modèles `ref_*finance*` sont importés.
 * Toutes les requêtes filtrent explicitement `instance_id` et bypassent le
 * global scope pour être indépendantes du CurrentInstance courant.
 */
final class EloquentFinanceReader implements FinanceReader
{
    private const SCALE = 2;

    public function find(int $instanceId, int $documentId): ?FinanceDto
    {
        $document = FinanceDocument::query()
            ->withoutGlobalScope(InstanceScope::class)
            ->where('instance_id', $instanceId)
            ->whereKey($documentId)
            ->first();

        return $document ? self::mapToDto($document) : null;
    }

    public function getByLink(int $instanceId, string $linkType, int $localId): ?FinanceDto
    {
        $link = FinanceDocumentLink::withoutInstanceScope()
            ->where('instance_id', $instanceId)
            ->where('linkable_type', $linkType)
            ->where('linkable_id', $localId)
            ->first();

        if ($link === null) {
            return null;
        }

        return $this->find($instanceId, (int) $link->getAttribute('document_id'));
    }

    /**
     * @return array{ttc: string, paid: string, due: string}
     */
    public function totalsForInstance(int $instanceId): array
    {
        $documents = FinanceDocument::query()
            ->withoutGlobalScope(InstanceScope::class)
            ->where('instance_id', $instanceId)
            ->where('is_cancelled', false)
            ->get(['doc_type', 'amount_ttc', 'paid_amount', 'due_amount']);

        $ttc = '0';
        $paid = '0';
        $due = '0';

        foreach ($documents as $document) {
            $sign = $document->getAttribute('doc_type') === 'credit_note' ? '-1' : '1';

            $ttc = bcadd($ttc, bcmul((string) $document->getAttribute('amount_ttc'), $sign, self::SCALE), self::SCALE);
            $paid = bcadd($paid, bcmul((string) $document->getAttribute('paid_amount'), $sign, self::SCALE), self::SCALE);
            $due = bcadd($due, bcmul((string) $document->getAttribute('due_amount'), $sign, self::SCALE), self::SCALE);
        }

        return ['ttc' => $ttc, 'paid' => $paid, 'due' => $due];
    }

    public static function mapToDto(FinanceDocument $d): FinanceDto
    {
        return new FinanceDto(
            id: (int) $d->getAttribute('id'),
            instanceId: (int) $d->getAttribute('instance_id'),
            documentUid: (string) $d->getAttribute('document_uid'),
            partyId: self::intOrNull($d->getAttribute('party_id')),
            docType: (string) $d->getAttribute('doc_type'),
            documentNumber: (string) $d->getAttribute('document_number'),
            currency: (string) $d->getAttribute('currency'),
            amountHt: (string) $d->getAttribute('amount_ht'),
            amountTax: (string) $d->getAttribute('amount_tax'),
            amountTtc: (string) $d->getAttribute('amount_ttc'),
            paidAmount: (string) $d->getAttribute('paid_amount'),
            dueAmount: (string) $d->getAttribute('due_amount'),
            statusNormalized: (string) $d->getAttribute('status_normalized'),
            isCancelled: (bool) $d->getAttribute('is_cancelled'),
            issuedAt: self::str($d->getAttribute('issued_at')),
            dueDate: self::str($d->getAttribute('due_date')),
            sourceModule: (string) $d->getAttribute('source_module'),
        );
    }

    private static function intOrNull(mixed $value): ?int
    {
        return $value !== null ? (int) $value : null;
    }

    private static function str(mixed $value): ?string
    {
        return $value !== null ? (string) $value : null;
    }
}
