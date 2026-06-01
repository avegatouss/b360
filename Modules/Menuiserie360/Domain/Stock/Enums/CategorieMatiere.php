<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Stock\Enums;

enum CategorieMatiere: string
{
    case PROFILE_ALU = 'profile_alu';
    case VITRAGE = 'vitrage';
    case ACCESSOIRE = 'accessoire';
    case AUTRE = 'autre';
}
