<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Sales\Enums;

enum StatutBonCommande: string
{
    case CREE = 'cree';
    case EN_PRODUCTION = 'en_production';
    case LIVRE = 'livre';
    case CLOTURE = 'cloture';
    case ANNULE = 'annule';
}
