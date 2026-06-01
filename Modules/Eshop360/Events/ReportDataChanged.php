<?php

namespace Modules\Eshop360\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired when data affecting reports changes (orders, stock, expenses, etc.).
 * Used to invalidate report caches.
 */
class ReportDataChanged
{
    use Dispatchable;

    /**
     * @param  int  $instanceId  The instance whose reports need invalidation
     * @param  string  $domain  The affected report domain: 'sales', 'stock', 'finance', 'all'
     */
    public function __construct(
        public readonly int $instanceId,
        public readonly string $domain = 'all',
    ) {
    }
}
