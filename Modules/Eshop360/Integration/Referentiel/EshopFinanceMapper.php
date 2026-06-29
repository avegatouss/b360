<?php

declare(strict_types=1);

namespace Modules\Eshop360\Integration\Referentiel;

use Modules\Eshop360\Domain\Finance\Models\Invoice;
use Modules\Referentiel360\Contracts\Finance\FinanceAttributesDto;

/**
 * Lot 3.b (ADR-031) — Mapper unique Eshop360 (Invoice) → FinanceAttributesDto.
 *
 * Partagé par l'observer (push temps réel sur created/updated) et par le
 * FinanceSource (backfill). Convertit une Invoice locale Eshop360 (table
 * `eshop_invoices`) en DTO neutre d'entrée pour le registre financier miroir
 * Referentiel360. Aucune logique d'écriture ici.
 *
 * 1 document miroir par ROW (localId = id de l'invoice). docType est toujours
 * 'invoice' : le modèle Eshop360 ne porte pas de notion d'avoir (credit_note) —
 * pas de colonne dédiée. La devise est lue depuis `currency_code` (snapshot
 * multi-devises Currency phase 2) avec repli 'XOF'.
 *
 * Le tiers golden est résolu côté Referentiel360 via le couple
 * (partyLinkType='eshop.customer', partyLocalId=customer_id) — cohérent avec le
 * Lot 1.b ({@see EshopCustomerPartySource}).
 *
 * N'importe QUE la surface publique `Contracts\Finance\*` de Referentiel360.
 */
final class EshopFinanceMapper
{
    /**
     * Invoice → FinanceAttributesDto.
     *
     * Montants castés en ?string (type DTO, échelle décimale préservée).
     * isCancelled dérivé de status === 'cancelled'. issuedAt = created_at ISO.
     */
    public function fromInvoice(Invoice $invoice): FinanceAttributesDto
    {
        $localId = (int) $invoice->getKey();
        $customerId = $invoice->getAttribute('customer_id');

        return new FinanceAttributesDto(
            localId: $localId,
            documentNumber: self::nullableString($invoice->getAttribute('invoice_number')) ?? 'INV-'.$localId,
            docType: 'invoice',
            currency: self::nullableString($invoice->getAttribute('currency_code')) ?? 'XOF',
            amountHt: self::nullableString($invoice->getAttribute('subtotal')) ?? '0',
            amountTax: self::nullableString($invoice->getAttribute('tax_amount')) ?? '0',
            amountTtc: self::nullableString($invoice->getAttribute('total')) ?? '0',
            paidAmount: self::nullableString($invoice->getAttribute('paid_amount')) ?? '0',
            issuedAt: self::nullableString($invoice->getAttribute('created_at')),
            dueDate: self::nullableString($invoice->getAttribute('due_date')),
            isCancelled: $invoice->getAttribute('status') === 'cancelled',
            partyLinkType: 'eshop.customer',
            partyLocalId: $customerId !== null ? (int) $customerId : null,
            sourceModule: 'eshop',
        );
    }

    /**
     * Normalise une valeur d'attribut en ?string : null si vide après trim.
     */
    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        $string = is_string($value) ? trim($value) : trim((string) $value);

        return $string === '' ? null : $string;
    }
}
