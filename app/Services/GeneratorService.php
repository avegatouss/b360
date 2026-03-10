<?php

namespace App\Services;

/**
 * Génère des références uniques pour les entités métier.
 */
class GeneratorService
{
    /**
     * Génère une référence unique pour un PersonneRole.
     *
     * Format : {PREFIX}-{PERSONNE_ID}-{TIMESTAMP}
     * Ex : PROP-42-20260226143022
     */
    public function generateForPersonneRole(int $personneMoraleId, string $role): string
    {
        $prefixes = [
            'proprietaire' => 'PROP',
            'locataire'    => 'LOC',
        ];

        $prefix = $prefixes[$role] ?? strtoupper(substr($role, 0, 4));

        return sprintf('%s-%d-%s', $prefix, $personneMoraleId, now()->format('YmdHis'));
    }
}
