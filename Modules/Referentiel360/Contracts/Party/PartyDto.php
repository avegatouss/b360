<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Contracts\Party;

/**
 * DTO immutable de LECTURE du golden record tiers (ADR-030, pattern ADR-021).
 *
 * Surface publique consommée par les modules L3. Ajout d'un champ optionnel =
 * non-breaking ; retrait / changement de type = breaking (ADR de remplacement).
 */
final readonly class PartyDto
{
    public function __construct(
        public int $id,
        public int $instanceId,
        public string $partyUid,
        public bool $isCustomer,
        public bool $isSupplier,
        public string $personType,
        public string $displayName,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $legalName,
        public ?string $email,
        public ?string $phone,
        public ?string $phoneSecondary,
        public ?string $address,
        public ?string $city,
        public string $country,
        public ?string $taxIdRccm,
        public ?string $taxIdNif,
        public ?string $supplierCategory,
        public ?string $paymentTerms,
        public ?int $leadTimeDays,
        public ?string $currency,
        public bool $isActive,
        public ?string $notes,
        public ?string $sourceModule,
    ) {}
}
