<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Commercial\DTOs;

/**
 * Résultat agrégé du calcul d'un devis menuiserie.
 *
 * Tous les montants en XOF (Côte d'Ivoire). Marge exprimée en fraction
 * (0.18 = 18%, pas 18).
 */
final readonly class DevisCalculationResult
{
    /**
     * @param  array<int, DevisLineCalculationDto>  $lines
     */
    public function __construct(
        public float $montantHt,
        public float $tauxTva,
        public float $montantTva,
        public float $montantTtc,
        public float $remiseGlobale,
        public float $coutRevientTotal,
        public float $margeBrute,
        public float $margeFraction,
        public array $lines,
    ) {}
}
