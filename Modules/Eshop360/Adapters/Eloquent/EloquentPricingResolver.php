<?php

declare(strict_types=1);

namespace Modules\Eshop360\Adapters\Eloquent;

use Modules\Eshop360\Contracts\Pricing\PricingRequestDto;
use Modules\Eshop360\Contracts\Pricing\PricingResolver;
use Modules\Eshop360\Contracts\Pricing\PricingResultDto;
use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Eshop360\Pricing\DTOs\LineItemPrice;
use Modules\Eshop360\Pricing\DTOs\PricingContext;
use Modules\Eshop360\Pricing\Engines\PricingEngine;

/**
 * Implémentation par défaut du {@see PricingResolver} via le moteur
 * interne {@see PricingEngine} (zone L1 protégée).
 *
 * Le moteur n'est **pas** modifié — cet adapter ne fait que :
 *   1. lire le produit (prix de base, pght, wholesale, tax) via Eloquent ;
 *   2. construire un {@see PricingContext} interne ;
 *   3. invoquer `PricingEngine::calculateLine()` ;
 *   4. mapper le {@see LineItemPrice} interne vers le {@see PricingResultDto} public.
 *
 * Les détails de marge canal (`marginTotal`, `partOwner`, `partChannel`,
 * `partDebt`) ne sont **pas** propagés au DTO public — confidentialité
 * intra-Eshop360.
 *
 * Bind par défaut dans
 * {@see \Modules\Eshop360\Providers\Eshop360ServiceProvider::register()}.
 */
final class EloquentPricingResolver implements PricingResolver
{
    public function __construct(
        private readonly PricingEngine $engine,
    ) {}

    public function resolveForProduct(PricingRequestDto $request): PricingResultDto
    {
        $product = Product::withoutGlobalScopes()
            ->where('instance_id', $request->instanceId)
            ->where('id', $request->productId)
            ->first();

        if ($product === null) {
            throw new \DomainException(
                "Product {$request->productId} not found in instance {$request->instanceId}"
            );
        }

        $ctx = new PricingContext(
            instanceId: $request->instanceId,
            productId: $request->productId,
            basePrice: (float) $product->getAttribute('price'),
            costPrice: (float) $product->getAttribute('cost_price'),
            pght: (float) $product->getAttribute('pght'),
            wholesalePrice: (float) $product->getAttribute('wholesale_price'),
            taxRate: (float) $product->getAttribute('tax_rate'),
            taxInclusive: (bool) $product->getAttribute('tax_inclusive'),
            quantity: $request->quantity,
            customerId: $request->customerId,
            channelId: $request->channelId,
            couponCode: $request->couponCode,
        );

        $line = $this->engine->calculateLine($ctx);

        return $this->mapToDto($request->productId, $request->quantity, $line);
    }

    private function mapToDto(int $productId, int $quantity, LineItemPrice $line): PricingResultDto
    {
        return new PricingResultDto(
            productId: $productId,
            quantity: $quantity,
            unitPrice: $line->unitPrice,
            discountAmount: $line->discountAmount,
            taxAmount: $line->taxAmount,
            total: $line->total,
            appliedRules: $line->appliedRules,
        );
    }
}
