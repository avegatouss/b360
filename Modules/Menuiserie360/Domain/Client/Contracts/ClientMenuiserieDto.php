<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Client\Contracts;

/**
 * DTO Client menuiserie autonome (ADR-023).
 */
final readonly class ClientMenuiserieDto
{
    public function __construct(
        public int $id,
        public int $instanceId,
        public string $code,
        public string $name,
        public string $type,
        public ?string $nom,
        public ?string $prenom,
        public ?string $raisonSociale,
        public ?string $email,
        public ?string $phone,
        public ?string $secondaryPhone,
        public ?string $address,
        public ?string $city,
        public string $country,
        public ?string $rccm,
        public ?string $nif,
        public string $statut,
        public bool $isActive,
        public ?int $legacyEshopCustomerId = null,
        public ?string $preferredContactMethod = null,
        public int $totalChantiersCount = 0,
        public float $totalRevenueXof = 0.0,
    ) {}
}
