<?php

declare(strict_types=1);

namespace Modules\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductPricingRecalculated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $updatedFields  Changed fields (e.g. ['wholesale_price' => 1500.00])
     */
    public function __construct(
        public readonly int $productId,
        public readonly int $instanceId,
        public readonly array $updatedFields = [],
    ) {}
}
