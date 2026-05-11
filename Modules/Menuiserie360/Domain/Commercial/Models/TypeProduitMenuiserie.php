<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Commercial\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Database\Traits\BelongsToInstance;

/**
 * P2-2 — Type de produit menuiserie (bibliothèque réutilisable).
 *
 * @phpstan-type TypeProduitFactory \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Menuiserie360\Domain\Commercial\Models\TypeProduitMenuiserie>
 */
class TypeProduitMenuiserie extends Model
{
    /** @use HasFactory<TypeProduitFactory> */
    use BelongsToInstance, HasFactory, SoftDeletes;

    protected $table = 'mnu_types_produits_menuiserie';

    protected $fillable = [
        'instance_id',
        'code',
        'nom',
        'categorie',
        'description',
        'largeur_standard_mm',
        'hauteur_standard_mm',
        'prix_indicatif_ht',
        'cout_indicatif',
        'matieres_principales',
        'is_active',
    ];

    protected $casts = [
        'largeur_standard_mm' => 'integer',
        'hauteur_standard_mm' => 'integer',
        'prix_indicatif_ht' => 'decimal:2',
        'cout_indicatif' => 'decimal:2',
        'matieres_principales' => 'array',
        'is_active' => 'boolean',
    ];
}
