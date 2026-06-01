<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Finance\Enums;

enum StatutFacture: string
{
    case DRAFT = 'draft';
    case ISSUED = 'issued';
    case PAID_PARTIAL = 'paid_partial';
    case PAID_FULL = 'paid_full';
    case CANCELLED = 'cancelled';
}
