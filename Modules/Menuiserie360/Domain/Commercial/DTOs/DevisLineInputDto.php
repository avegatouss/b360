<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Commercial\DTOs;

/**
 * Input ligne de devis pour DevisCalculatorService::calculate().
 *
 * Sépare l'input applicatif (calcul) des modèles Eloquent persistés
 * (LigneDevis) — facilite les tests unitaires sans toucher la DB.
 */
final readonly class DevisLineInputDto
{
    public function __construct(
        public string $designation,
        public int $quantite,
        public float $prixUnitaireHt,
        public float $coutRevientUnitaire,
        public float $remiseLigne = 0.0,
        public ?int $largeurMm = null,
        public ?int $hauteurMm = null,
        public ?int $matiereId = null,
    ) {}
}
