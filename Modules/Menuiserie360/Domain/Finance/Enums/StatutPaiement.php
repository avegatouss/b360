<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Finance\Enums;

enum StatutPaiement: string
{
    case PENDING = 'pending';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
}
