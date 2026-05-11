<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Chantier\Enums;

enum StatutChantier: string
{
    case EN_ATTENTE = 'en_attente';
    case EN_COURS = 'en_cours';
    case SUSPENDU = 'suspendu';
    case TERMINE = 'termine';
    case LIVRE = 'livre';
    case ANNULE = 'annule';
}
