<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\DTOs;

use DateTimeImmutable;

/**
 * Immutable context that travels through the pricing pipeline.
 * Carries all information a rule might need to make its decision.
 */
readonly class PricingContext
{
    public function __construct(
        public int                $instanceId,
        public int                $productId,
        public float              $basePrice,
        public float              $costPrice,
        public float              $pght,
        public float              $wholesalePrice,
        public float              $taxRate,
        public bool               $taxInclusive,
        public int                $quantity,
        public ?int               $customerId     = null,
        public ?int               $channelId      = null,
        public ?string            $couponCode     = null,
        public ?string            $discountType   = null,
        public float              $discountValue  = 0.0,
        public DateTimeImmutable  $evaluatedAt    = new DateTimeImmutable(),
    ) {}

    /**
     * Create a context from a product model array and optional overrides.
     *
     * @param array<string, mixed> $product  Product attributes (or toArray())
     * @param array<string, mixed> $overrides
     */
    public static function fromProduct(array $product, array $overrides = []): self
    {
        return new self(
            instanceId:    (int) ($overrides['instanceId']    ?? $product['instance_id']    ?? 0),
            productId:     (int) ($overrides['productId']     ?? $product['id']             ?? 0),
            basePrice:     (float) ($overrides['basePrice']   ?? $product['price']          ?? 0),
            costPrice:     (float) ($overrides['costPrice']   ?? $product['cost_price']     ?? 0),
            pght:          (float) ($overrides['pght']        ?? $product['pght']           ?? 0),
            wholesalePrice:(float) ($overrides['wholesalePrice'] ?? $product['wholesale_price'] ?? 0),
            taxRate:       (float) ($overrides['taxRate']     ?? $product['tax_rate']       ?? 0),
            taxInclusive:  (bool) ($overrides['taxInclusive'] ?? $product['tax_inclusive']  ?? false),
            quantity:      (int) ($overrides['quantity']      ?? 1),
            customerId:    $overrides['customerId']  ?? null,
            channelId:     $overrides['channelId']   ?? null,
            couponCode:    $overrides['couponCode']  ?? null,
            discountType:  $overrides['discountType'] ?? ($product['discount_type'] ?? null),
            discountValue: (float) ($overrides['discountValue'] ?? $product['discount_value'] ?? 0),
            evaluatedAt:   $overrides['evaluatedAt'] ?? new DateTimeImmutable(),
        );
    }

    /**
     * Build a cache key that uniquely identifies this pricing request.
     */
    public function cacheKey(): string
    {
        return sprintf(
            'pricing:%d:%d:%d:ch%s:q%d',
            $this->instanceId,
            $this->productId,
            (int) ($this->basePrice * 100),
            $this->channelId ?? '0',
            $this->quantity,
        );
    }

    /**
     * Whether this context targets a distribution channel.
     */
    public function isChannelSale(): bool
    {
        return $this->channelId !== null;
    }

    /**
     * Whether a coupon is applied (disables cache).
     */
    public function hasCoupon(): bool
    {
        return $this->couponCode !== null && $this->couponCode !== '';
    }
}
