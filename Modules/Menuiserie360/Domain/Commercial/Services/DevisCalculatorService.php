<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Commercial\Services;

use InvalidArgumentException;
use Modules\Menuiserie360\Domain\Commercial\DTOs\DevisCalculationResult;
use Modules\Menuiserie360\Domain\Commercial\DTOs\DevisLineCalculationDto;
use Modules\Menuiserie360\Domain\Commercial\DTOs\DevisLineInputDto;
use Modules\Menuiserie360\Domain\Commercial\Exceptions\MargeInsuffisanteException;

/**
 * P1-7 — Calcul d'un devis menuiserie aluminium.
 *
 * Service PUR (sans I/O — testable sans DB). Reçoit en entrée des DTO
 * immutables, retourne un DTO immutable de résultat.
 *
 * Règles métier :
 *   - Montant ligne HT = (prix_unitaire * quantité) - remise_ligne, plancher 0
 *   - Remise globale appliquée au montant HT total avant TVA
 *   - TVA = HT_après_remise_globale * taux_tva (par défaut 18% CI)
 *   - Marge brute = HT_avant_remise_globale - cout_revient_total
 *   - Marge en fraction = marge_brute / HT_avant_remise (0 si HT = 0)
 *   - `enforceMarge=true` lève MargeInsuffisanteException si marge < seuil
 *
 * Précision : tous les montants sont arrondis à 2 décimales en sortie
 * (XOF — pas de centime mais cohérence avec stockage decimal(14,2)).
 *
 * Helpers : surfaceM2() et perimetreLineaire() depuis dimensions mm,
 * suggestPriceFromMatiere() pour pré-remplir un prix depuis le coût matière.
 */
final class DevisCalculatorService
{
    private const TVA_DEFAUT_CI = 0.18;

    private const MARGE_MINIMUM_DEFAUT = 0.15;

    /**
     * Calcule le devis complet à partir des lignes input.
     *
     * @param  array<int, DevisLineInputDto>  $lines
     */
    public function calculate(
        array $lines,
        float $remiseGlobale = 0.0,
        float $tauxTva = self::TVA_DEFAUT_CI,
        float $margeMinimum = self::MARGE_MINIMUM_DEFAUT,
        bool $enforceMarge = false,
    ): DevisCalculationResult {
        $this->guardTauxTva($tauxTva);
        $this->guardRemiseGlobale($remiseGlobale);
        $this->guardMargeMinimum($margeMinimum);

        $linesCalc = [];
        $montantHtBrut = 0.0;
        $coutRevientTotal = 0.0;

        foreach ($lines as $line) {
            $this->guardQuantite($line->quantite);
            $this->guardPrixUnitaire($line->prixUnitaireHt);
            $this->guardCoutRevient($line->coutRevientUnitaire);

            $brut = $line->prixUnitaireHt * $line->quantite;
            $apresRemise = max(0.0, $brut - $line->remiseLigne);

            $coutLigne = $line->coutRevientUnitaire * $line->quantite;

            $linesCalc[] = new DevisLineCalculationDto(
                designation: $line->designation,
                quantite: $line->quantite,
                prixUnitaireHt: $line->prixUnitaireHt,
                remiseLigne: $line->remiseLigne,
                montantHt: $this->round2($apresRemise),
                coutRevientLigne: $this->round2($coutLigne),
                surfaceM2: $this->surfaceM2($line->largeurMm, $line->hauteurMm),
                perimetreLineaire: $this->perimetreLineaire($line->largeurMm, $line->hauteurMm),
            );

            $montantHtBrut += $apresRemise;
            $coutRevientTotal += $coutLigne;
        }

        // Remise globale ne peut excéder le HT brut.
        $remiseGlobaleEffective = min($remiseGlobale, $montantHtBrut);

        $montantHtNet = max(0.0, $montantHtBrut - $remiseGlobaleEffective);
        $montantTva = $montantHtNet * $tauxTva;
        $montantTtc = $montantHtNet + $montantTva;

        $margeBrute = $montantHtBrut - $coutRevientTotal;
        $margeFraction = $montantHtBrut > 0
            ? $margeBrute / $montantHtBrut
            : 0.0;

        if ($enforceMarge && $montantHtBrut > 0 && $margeFraction < $margeMinimum) {
            throw MargeInsuffisanteException::below($margeFraction, $margeMinimum);
        }

        return new DevisCalculationResult(
            montantHt: $this->round2($montantHtNet),
            tauxTva: $tauxTva,
            montantTva: $this->round2($montantTva),
            montantTtc: $this->round2($montantTtc),
            remiseGlobale: $this->round2($remiseGlobaleEffective),
            coutRevientTotal: $this->round2($coutRevientTotal),
            margeBrute: $this->round2($margeBrute),
            margeFraction: round($margeFraction, 4),
            lines: $linesCalc,
        );
    }

    /**
     * Surface en m² depuis dimensions en mm. null si une dimension manque.
     */
    public function surfaceM2(?int $largeurMm, ?int $hauteurMm): ?float
    {
        if ($largeurMm === null || $hauteurMm === null) {
            return null;
        }

        return round(($largeurMm / 1000.0) * ($hauteurMm / 1000.0), 4);
    }

    /**
     * Périmètre en mètres linéaires (4 côtés) depuis dimensions mm.
     */
    public function perimetreLineaire(?int $largeurMm, ?int $hauteurMm): ?float
    {
        if ($largeurMm === null || $hauteurMm === null) {
            return null;
        }

        return round(2.0 * (($largeurMm / 1000.0) + ($hauteurMm / 1000.0)), 4);
    }

    /**
     * Suggère un prix unitaire HT à partir d'un coût matière unitaire en
     * appliquant la marge minimum. Utile pour pré-remplir un devis depuis
     * une matière première du catalogue.
     */
    public function suggestPriceFromMatiere(float $coutUnitaire, float $margeMinimum = self::MARGE_MINIMUM_DEFAUT): float
    {
        $this->guardCoutRevient($coutUnitaire);
        $this->guardMargeMinimum($margeMinimum);

        // Prix HT minimum = cout / (1 - marge) — formule classique marge sur PV
        if ($margeMinimum >= 1.0) {
            throw new InvalidArgumentException('Marge minimum doit être strictement inférieure à 1 (100%).');
        }

        return $this->round2($coutUnitaire / (1.0 - $margeMinimum));
    }

    // ─── Guards ──────────────────────────────────────────────────

    private function guardQuantite(int $quantite): void
    {
        if ($quantite <= 0) {
            throw new InvalidArgumentException('La quantité doit être strictement positive.');
        }
    }

    private function guardPrixUnitaire(float $prix): void
    {
        if ($prix < 0) {
            throw new InvalidArgumentException('Le prix unitaire ne peut être négatif.');
        }
    }

    private function guardCoutRevient(float $cout): void
    {
        if ($cout < 0) {
            throw new InvalidArgumentException('Le coût de revient ne peut être négatif.');
        }
    }

    private function guardRemiseGlobale(float $remise): void
    {
        if ($remise < 0) {
            throw new InvalidArgumentException('La remise globale ne peut être négative.');
        }
    }

    private function guardTauxTva(float $taux): void
    {
        if ($taux < 0 || $taux > 1) {
            throw new InvalidArgumentException('Le taux de TVA doit être entre 0 et 1 (fraction, ex. 0.18).');
        }
    }

    private function guardMargeMinimum(float $marge): void
    {
        if ($marge < 0 || $marge > 1) {
            throw new InvalidArgumentException('La marge minimum doit être entre 0 et 1 (fraction).');
        }
    }

    private function round2(float $value): float
    {
        return round($value, 2);
    }
}
