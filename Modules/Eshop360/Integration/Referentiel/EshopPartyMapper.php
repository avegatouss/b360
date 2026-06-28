<?php

declare(strict_types=1);

namespace Modules\Eshop360\Integration\Referentiel;

use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Eshop360\Domain\Purchasing\Models\Supplier;
use Modules\Referentiel360\Contracts\Party\PartyAttributesDto;

/**
 * Lot 1.b (ADR-030) — Mapper unique Eshop360 → PartyAttributesDto.
 *
 * Partagé par les observers (push temps réel sur created/updated) et par les
 * PartySource (backfill). Convertit un modèle local Eshop360 en DTO neutre
 * d'entrée pour le golden record Referentiel360. Aucune logique d'écriture ici.
 *
 * Multi-canal tranché : 1 party par ROW (localId = id du customer/supplier).
 * Le channel_id est ignoré (le référentiel est scopé instance).
 *
 * N'importe QUE la surface publique `Contracts\Party\*` de Referentiel360.
 */
final class EshopPartyMapper
{
    /**
     * Customer → PartyAttributesDto (rôle customer).
     *
     * Eshop customer n'a qu'un `name` unique (pas de split firstName/lastName).
     * personType déduit de la présence d'un company_name.
     */
    public function fromCustomer(Customer $customer): PartyAttributesDto
    {
        $companyName = self::nullableString($customer->getAttribute('company_name'));

        return new PartyAttributesDto(
            localId: (int) $customer->getKey(),
            isCustomer: true,
            personType: $companyName !== null ? 'entreprise' : 'particulier',
            displayName: self::nullableString($customer->getAttribute('name')) ?? '',
            legalName: $companyName,
            email: self::nullableString($customer->getAttribute('email')),
            phone: self::nullableString($customer->getAttribute('phone')),
            address: self::nullableString($customer->getAttribute('address')),
            city: self::nullableString($customer->getAttribute('city')),
            country: self::normalizeCountry(self::nullableString($customer->getAttribute('country'))),
            taxIdNif: self::nullableString($customer->getAttribute('tax_number')),
            notes: self::nullableString($customer->getAttribute('notes')),
            sourceModule: 'eshop',
        );
    }

    /**
     * Supplier → PartyAttributesDto (rôle supplier).
     *
     * personType forcé à 'entreprise' (un fournisseur Eshop est toujours une
     * structure). `contact_person` et `balance` sont ignorés (pas de champ DTO
     * correspondant).
     */
    public function fromSupplier(Supplier $supplier): PartyAttributesDto
    {
        return new PartyAttributesDto(
            localId: (int) $supplier->getKey(),
            isSupplier: true,
            personType: 'entreprise',
            displayName: self::nullableString($supplier->getAttribute('name')) ?? '',
            legalName: self::nullableString($supplier->getAttribute('company')),
            email: self::nullableString($supplier->getAttribute('email')),
            phone: self::nullableString($supplier->getAttribute('phone')),
            address: self::nullableString($supplier->getAttribute('address')),
            country: self::normalizeCountry(self::nullableString($supplier->getAttribute('country'))),
            notes: self::nullableString($supplier->getAttribute('notes')),
            sourceModule: 'eshop',
        );
    }

    /**
     * Normalise un pays en code ISO-2 majuscule.
     *
     * trim ; si longueur == 2 → strtoupper ; sinon fallback 'CI'. La valeur
     * source reste intacte dans eshop_* (non destructif) : seul le DTO neutre
     * porte le code normalisé.
     */
    private static function normalizeCountry(?string $country): string
    {
        $value = self::nullableString($country);

        if ($value !== null && mb_strlen($value) === 2) {
            return mb_strtoupper($value);
        }

        return 'CI';
    }

    /**
     * Normalise une valeur d'attribut en ?string : null si vide après trim.
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
