<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Client\Enums;

enum StatutClientMenuiserie: string
{
    case LEAD = 'lead';
    case QUALIFIE = 'qualifie';
    case CONVERTI = 'converti';
    case PERDU = 'perdu';
    case CONTENTIEUX = 'contentieux';
    case ARCHIVE = 'archive';

    public function label(): string
    {
        return match ($this) {
            self::LEAD => 'Lead',
            self::QUALIFIE => 'Qualifie',
            self::CONVERTI => 'Converti',
            self::PERDU => 'Perdu',
            self::CONTENTIEUX => 'Contentieux',
            self::ARCHIVE => 'Archive',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LEAD => 'secondary',
            self::QUALIFIE => 'primary',
            self::CONVERTI => 'success',
            self::PERDU => 'warning',
            self::CONTENTIEUX => 'danger',
            self::ARCHIVE => 'dark',
        };
    }
}
