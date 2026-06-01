<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Commercial\Exceptions;

use DomainException;

/**
 * Devis dont la marge calculée est inférieure au seuil minimum configuré.
 *
 * Levée par {@see \Modules\Menuiserie360\Domain\Commercial\Services\DevisCalculatorService}
 * quand le calcul du devis aboutit à une marge brute < `marge_minimum`
 * (du devis, ou par défaut 15%).
 */
final class MargeInsuffisanteException extends DomainException
{
    public static function below(float $margeReelle, float $margeMinimum): self
    {
        $reelle = number_format($margeReelle * 100, 2);
        $min = number_format($margeMinimum * 100, 2);

        return new self("Marge insuffisante : {$reelle}% < seuil minimum {$min}%.");
    }
}
