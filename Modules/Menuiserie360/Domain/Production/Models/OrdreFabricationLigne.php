<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Production\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;

/**
 * P2-8 — Ligne d'ordre de fabrication.
 *
 * @phpstan-type OfLigneFactory \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Menuiserie360\Domain\Production\Models\OrdreFabricationLigne>
 */
class OrdreFabricationLigne extends Model
{
    /** @use HasFactory<OfLigneFactory> */
    use BelongsToInstance, HasFactory;

    protected $table = 'mnu_of_lignes';

    protected $fillable = [
        'instance_id',
        'of_id',
        'bc_item_id',
        'designation',
        'quantite',
        'largeur_mm',
        'hauteur_mm',
        'statut_ligne',
    ];

    protected $casts = [
        'quantite' => 'integer',
        'largeur_mm' => 'integer',
        'hauteur_mm' => 'integer',
    ];

    /**
     * @return BelongsTo<OrdreFabrication, $this>
     */
    public function ordre(): BelongsTo
    {
        return $this->belongsTo(OrdreFabrication::class, 'of_id');
    }

    /**
     * @return HasMany<DecoupeAluminium, $this>
     */
    public function decoupes(): HasMany
    {
        return $this->hasMany(DecoupeAluminium::class, 'of_ligne_id');
    }
}
