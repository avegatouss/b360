<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Stock\Exceptions;

use DomainException;

/**
 * Stock matière insuffisant pour la sortie/réservation demandée.
 *
 * Levée par {@see \Modules\Menuiserie360\Domain\Stock\Services\StockMatiereService}
 * quand `consume()` ou `reserve()` est appelé avec une quantité supérieure
 * au stock disponible (= actuel - réservé).
 *
 * Le scénario typique côté appelant : afficher un message clair à
 * l'utilisateur (chef de chantier, atelier) et bloquer la création de
 * l'OF / la validation de la sortie.
 */
final class StockInsuffisantException extends DomainException
{
    public static function forMatiere(int $matiereId, float $demande, float $disponible): self
    {
        return new self(
            "Stock insuffisant pour la matière #{$matiereId} : demandé {$demande}, disponible {$disponible}."
        );
    }
}
