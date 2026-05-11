<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Sales\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Traits\BelongsToInstance;

/**
 * P2-4 — Ligne de bon de commande (snapshot des lignes devis).
 *
 * @phpstan-type BcItemFactory \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Menuiserie360\Domain\Sales\Models\BonCommandeItem>
 */
class BonCommandeItem extends Model
{
    /** @use HasFactory<BcItemFactory> */
    use BelongsToInstance, HasFactory;

    protected $table = 'mnu_bc_items';

    protected $fillable = [
        'instance_id',
        'bc_id',
        'ligne_devis_source_id',
        'matiere_id',
        'designation',
        'quantite',
        'largeur_mm',
        'hauteur_mm',
        'prix_unitaire_ht',
        'montant_ht',
        'cout_revient',
        'ordre',
    ];

    protected $casts = [
        'quantite' => 'integer',
        'largeur_mm' => 'integer',
        'hauteur_mm' => 'integer',
        'prix_unitaire_ht' => 'decimal:4',
        'montant_ht' => 'decimal:2',
        'cout_revient' => 'decimal:2',
        'ordre' => 'integer',
    ];

    /**
     * @return BelongsTo<BonCommande, $this>
     */
    public function bonCommande(): BelongsTo
    {
        return $this->belongsTo(BonCommande::class, 'bc_id');
    }
}
