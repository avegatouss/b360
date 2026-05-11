<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Finance\Enums;

enum TypeFacture: string
{
    case ACOMPTE = 'acompte';
    case SOLDE = 'solde';
    case AVOIR = 'avoir';
}
