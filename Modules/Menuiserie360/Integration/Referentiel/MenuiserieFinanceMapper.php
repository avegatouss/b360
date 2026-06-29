<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Integration\Referentiel;

use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;
use Modules\Referentiel360\Contracts\Finance\FinanceAttributesDto;

/**
 * Lot 3.a (ADR-031, ZONE L1) — Mapper Menuiserie360 → FinanceAttributesDto.
 *
 * Partagé par l'observer (push temps réel sur created/updated) et par le
 * FinanceSource (backfill). Convertit une facture locale `mnu_invoices` en DTO
 * neutre d'entrée pour le registre financier miroir Referentiel360. Aucune
 * logique d'écriture ici.
 *
 * Montants en `?string` cohérent avec le DTO (cast décimal du modèle → string,
 * pas de float intermédiaire). Le tiers est résolu côté Writer via le lien
 * `mnu.client` (PartyReader interne à Referentiel360).
 *
 * N'importe QUE la surface publique `Contracts\Finance\*` de Referentiel360.
 */
final class MenuiserieFinanceMapper
{
    /**
     * MenuiserieInvoice → FinanceAttributesDto.
     *
     * `docType` : type 'avoir' → 'credit_note', sinon 'invoice'.
     * `dueDate` : null (pas de colonne échéance sur `mnu_invoices`).
     * `isCancelled` : status === 'cancelled'.
     */
    public function fromInvoice(MenuiserieInvoice $invoice): FinanceAttributesDto
    {
        return new FinanceAttributesDto(
            localId: (int) $invoice->getKey(),
            documentNumber: self::nullableString($invoice->getAttribute('invoice_number')) ?? '',
            docType: $invoice->getAttribute('type') === 'avoir' ? 'credit_note' : 'invoice',
            currency: 'XOF',
            amountHt: self::nullableString($invoice->getAttribute('amount_ht')) ?? '0',
            amountTax: self::nullableString($invoice->getAttribute('amount_tva')) ?? '0',
            amountTtc: self::nullableString($invoice->getAttribute('amount_ttc')) ?? '0',
            paidAmount: self::nullableString($invoice->getAttribute('paid_amount')) ?? '0',
            issuedAt: self::nullableString($invoice->getAttribute('issued_at')),
            dueDate: null,
            isCancelled: $invoice->getAttribute('status') === 'cancelled',
            partyLinkType: 'mnu.client',
            partyLocalId: (int) $invoice->getAttribute('client_id'),
            sourceModule: 'menuiserie',
        );
    }

    /**
     * Normalise une valeur d'attribut en ?string : null si vide après trim.
     *
     * Les casts décimaux et datetime sont stringifiés (Carbon → ISO).
     */
    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = is_string($value) ? trim($value) : trim((string) $value);

        return $string === '' ? null : $string;
    }
}
