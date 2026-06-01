<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Stock\Enums;

/**
 * Unités de mesure des matières premières menuiserie.
 *
 * - METRE_LINEAIRE : profilés aluminium (longueur).
 * - METRE_CARRE    : vitrages, panneaux (surface).
 * - PIECE          : accessoires, quincaillerie (unité).
 */
enum UniteMesure: string
{
    case METRE_LINEAIRE = 'm_lineaire';
    case METRE_CARRE = 'm2';
    case PIECE = 'piece';
}
