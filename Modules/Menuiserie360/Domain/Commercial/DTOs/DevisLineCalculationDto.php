<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Commercial\DTOs;

/**
 * Résultat calculé d'une ligne de devis (snapshot après application
 * des règles : prix unitaire, remise ligne, montant HT, coût revient).
 */
final readonly class DevisLineCalculationDto
{
    public function __construct(
        public string $designation,
        public int $quantite,
        public float $prixUnitaireHt,
        public float $remiseLigne,
        public float $montantHt,
        public float $coutRevientLigne,
        public ?float $surfaceM2,
        public ?float $perimetreLineaire,
    ) {}
}
