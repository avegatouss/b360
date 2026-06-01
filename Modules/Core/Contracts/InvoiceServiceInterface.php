<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

interface InvoiceServiceInterface
{
    public function createFromOrder(int $orderId): mixed;

    public function generateNumber(int $instanceId): string;
}
