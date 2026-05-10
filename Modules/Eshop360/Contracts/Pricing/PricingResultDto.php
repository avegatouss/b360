<?php

declare(strict_types=1);

namespace Modules\Eshop360\Contracts\Pricing;

/**
 * Immutable DTO de sortie : résultat d'une résolution de prix.
 *
 * Expose uniquement les valeurs nécessaires à un consommateur L4 — les
 * détails de marge canal (parts owner/channel/debt) restent confidentiels
 * à Eshop360 et ne sont **pas** exposés ici.
 *
 * ADR-021 §1 — surface publique.
 */
final readonly class PricingResultDto
{
    /**
     * @param  int  $productId  ID du produit résolu
     * @param  int  $quantity  Quantité demandée (recopiée pour traçabilité)
     * @param  float  $unitPrice  Prix unitaire après application des règles (cohérence
     *                            `taxInclusive` cf. config produit Eshop360)
     * @param  float  $discountAmount  Montant total de remise appliqué
     * @param  float  $taxAmount  Montant de taxe (TVA)
     * @param  float  $total  Total ligne final (`unit * qty - discount + tax` typique)
     * @param  array<int, array{slug: string, version: int, delta: float}>  $appliedRules
     *                                                                                     Audit trail des règles appliquées dans l'ordre
     */
    public function __construct(
        public int $productId,
        public int $quantity,
        public float $unitPrice,
        public float $discountAmount,
        public float $taxAmount,
        public float $total,
        public array $appliedRules,
    ) {}
}
