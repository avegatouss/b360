<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Production\Enums;

enum StatutOrdreFabrication: string
{
    case EN_ATTENTE = 'en_attente';
    case EN_COURS = 'en_cours';
    case TERMINE = 'termine';
    case CONTROLE = 'controle';
    case LIVRE = 'livre';
    case ANNULE = 'annule';
}
