<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Stock\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Database\Traits\BelongsToInstance;

/**
 * P1-1 — Matière première menuiserie aluminium.
 *
 * Catalogue des profilés (m linéaires), vitrages (m²) et accessoires (pièces).
 * Sémantique distincte d'`eshop_products` — ne pas confondre.
 *
 * Multi-tenant via `BelongsToInstance` (Core). Soft delete actif.
 *
 * @phpstan-type MatierePremiereFactory \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Menuiserie360\Domain\Stock\Models\MatierePremiere>
 */
class MatierePremiere extends Model
{
    /** @use HasFactory<MatierePremiereFactory> */
    use BelongsToInstance, HasFactory, SoftDeletes;

    protected $table = 'mnu_matieres_premieres';

    protected $fillable = [
        'instance_id',
        'code',
        'designation',
        'categorie',
        'unite',
        'prix_unitaire',
        'seuil_alerte',
        'fournisseur_principal',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:4',
        'seuil_alerte' => 'decimal:4',
        'is_active' => 'boolean',
    ];
}
