<?php

declare(strict_types=1);

namespace Modules\Eshop360\Pricing\Events;

/**
 * Alias for the Core event — ensures backward compatibility.
 *
 * Listeners should register against Modules\Core\Events\ProductPricingRecalculated
 * for cross-module interoperability (e.g., Menuiserie360 listening to pricing changes).
 *
 * @see \Modules\Core\Events\ProductPricingRecalculated
 */
class ProductPricingRecalculated extends \Modules\Core\Events\ProductPricingRecalculated
{
    /**
     * @param int                   $productId
     * @param int                   $instanceId
     * @param array<string, mixed>  $changes  Fields that were updated (e.g. ['wholesale_price' => 1500.00])
     */
    public function __construct(
        int   $productId,
        int   $instanceId,
        array $changes,
    ) {
        parent::__construct($productId, $instanceId, $changes);
    }
}
