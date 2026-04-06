<?php

declare(strict_types=1);

namespace Modules\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockAdjusted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $productId,
        public readonly int $warehouseId,
        public readonly int $instanceId,
        public readonly float $quantityDelta,
        public readonly string $type,
        public readonly string $reference,
    ) {}
}
