<?php

declare(strict_types=1);

namespace Modules\Eshop360\Contracts\Pricing;

/**
 * Contrat public de résolution de prix Eshop360 (ADR-021 §1).
 *
 * Façade publique pour le moteur de pricing interne (zone L1 protégée
 * cf. PROTECTED_AREAS.md). Aucun consommateur L4 n'accède directement
 * à `PricingEngine` ni aux DTO internes (`PricingContext`, `LineItemPrice`)
 * du namespace `Modules\Eshop360\Pricing\*`.
 *
 * L'implémentation par défaut est
 * {@see \Modules\Eshop360\Adapters\Eloquent\EloquentPricingResolver}.
 */
interface PricingResolver
{
    /**
     * Résout le prix d'un produit pour un contexte donné (canal, client,
     * coupon, quantité). L'adapter gère la lecture du produit (prix de base,
     * pght, wholesale_price, tax_rate, tax_inclusive) et délègue ensuite au
     * moteur de pricing interne.
     *
     * @throws \DomainException si le produit n'existe pas dans l'instance.
     */
    public function resolveForProduct(PricingRequestDto $request): PricingResultDto;
}
