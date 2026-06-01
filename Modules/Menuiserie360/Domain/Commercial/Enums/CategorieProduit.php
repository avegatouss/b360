<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Commercial\Enums;

enum CategorieProduit: string
{
    case FENETRE = 'fenetre';
    case BAIE_COULISSANTE = 'baie';
    case PORTE = 'porte';
    case GARDE_CORPS = 'garde_corps';
    case VERANDA = 'veranda';
    case AUTRE = 'autre';
}
