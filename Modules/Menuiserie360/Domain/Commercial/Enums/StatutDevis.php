<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Commercial\Enums;

/**
 * Workflow d'un devis menuiserie. Spec v1.3 §4.1 (BC-Commercial) :
 *   brouillon → soumis → validé (>seuil) → accepté/refusé → transformé
 */
enum StatutDevis: string
{
    case BROUILLON = 'brouillon';
    case SOUMIS = 'soumis';
    case VALIDE = 'valide';        // validé direction (au-dessus seuil)
    case ACCEPTE = 'accepte';
    case REFUSE = 'refuse';
    case TRANSFORME = 'transforme'; // → BC + OF + facture acompte créés
}
