<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Finance\Enums;

enum MethodePaiement: string
{
    case ESPECES = 'especes';
    case VIREMENT = 'virement';
    case MOBILE_MONEY = 'mobile_money';
    case CHEQUE = 'cheque';
}
