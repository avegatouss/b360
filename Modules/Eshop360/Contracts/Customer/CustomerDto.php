<?php

declare(strict_types=1);

namespace Modules\Eshop360\Contracts\Customer;

/**
 * Immutable DTO publié par Eshop360 pour la lecture client (CRM).
 *
 * Surface publique — voir ADR-021 §1. Convention de stabilité :
 *   - ajouter un champ optionnel = non-breaking
 *   - retirer ou changer un type = breaking (ADR de remplacement requis)
 *
 * Note : `walletBalance` et `creditLimit` sont exposés en lecture mais
 * **toute mutation** doit passer par `FinanceService` côté Eshop360 (zone L1
 * cf. PROTECTED_AREAS.md). Aucun consommateur L4 ne doit modifier ces champs.
 */
final readonly class CustomerDto
{
    public function __construct(
        public int $id,
        public int $instanceId,
        public ?int $channelId,
        public ?int $userId,
        public ?int $groupId,
        public string $code,
        public string $name,
        public ?string $email,
        public ?string $phone,
        public ?string $address,
        public ?string $city,
        public ?string $country,
        public ?string $companyName,
        public ?string $taxNumber,
        public float $walletBalance,
        public float $creditLimit,
        public bool $isActive,
    ) {}
}
