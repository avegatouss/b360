<?php

declare(strict_types=1);

namespace Modules\Eshop360\Contracts\Pricing;

/**
 * Immutable DTO d'entrée pour la résolution de prix.
 *
 * ADR-021 §1 — surface publique. Convention de stabilité :
 *   - ajouter un champ optionnel = non-breaking
 *   - rendre obligatoire un champ existant ou retirer = breaking
 */
final readonly class PricingRequestDto
{
    public function __construct(
        public int $instanceId,
        public int $productId,
        public int $quantity = 1,
        public ?int $channelId = null,
        public ?int $customerId = null,
        public ?string $couponCode = null,
    ) {}
}
