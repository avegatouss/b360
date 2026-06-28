<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Contracts\Party;

/**
 * DTO immutable d'ENTRÉE neutre (ADR-030).
 *
 * Poussé par les modules L3 vers `PartyWriter`/`PartyResolver` (et émis par
 * les `PartySource` pour le backfill). Referentiel360 ignore l'origine : il ne
 * reçoit qu'une identité normalisée + l'identifiant local du module.
 *
 * Les helpers de normalisation (email/phone) sont fournis ici pour que le
 * matcher et le writer partagent exactement la même règle.
 */
final readonly class PartyAttributesDto
{
    public function __construct(
        public int $localId,
        public bool $isCustomer = false,
        public bool $isSupplier = false,
        public string $personType = 'particulier',
        public string $displayName = '',
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $legalName = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $phoneSecondary = null,
        public ?string $address = null,
        public ?string $city = null,
        public string $country = 'CI',
        public ?string $taxIdRccm = null,
        public ?string $taxIdNif = null,
        public ?string $supplierCategory = null,
        public ?string $paymentTerms = null,
        public ?int $leadTimeDays = null,
        public ?string $currency = null,
        public ?string $notes = null,
        public ?string $sourceModule = null,
    ) {}

    /**
     * Email normalisé (lower/trim) ou null si vide. Clé de dédup #2.
     */
    public function normalizedEmail(): ?string
    {
        return self::normalizeEmail($this->email);
    }

    /**
     * Téléphone normalisé (digits uniquement) ou null si vide. Clé de dédup #3.
     */
    public function normalizedPhone(): ?string
    {
        return self::normalizePhone($this->phone);
    }

    public static function normalizeEmail(?string $email): ?string
    {
        if ($email === null) {
            return null;
        }
        $email = mb_strtolower(trim($email));

        return $email === '' ? null : $email;
    }

    public static function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        return $digits === '' ? null : $digits;
    }
}
