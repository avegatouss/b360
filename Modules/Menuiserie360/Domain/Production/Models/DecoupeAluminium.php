<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Production\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Menuiserie360\Domain\Stock\Models\MatierePremiere;

/**
 * P2-8 — Découpe aluminium effectuée (traçabilité atelier).
 *
 * @phpstan-type DecoupeFactory \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Menuiserie360\Domain\Production\Models\DecoupeAluminium>
 */
class DecoupeAluminium extends Model
{
    /** @use HasFactory<DecoupeFactory> */
    use BelongsToInstance, HasFactory;

    protected $table = 'mnu_decoupes';

    protected $fillable = [
        'instance_id',
        'of_ligne_id',
        'matiere_id',
        'longueur_mm',
        'quantite',
        'effectue_par',
        'decoupe_at',
    ];

    protected $casts = [
        'longueur_mm' => 'decimal:2',
        'quantite' => 'integer',
        'decoupe_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<OrdreFabricationLigne, $this>
     */
    public function ofLigne(): BelongsTo
    {
        return $this->belongsTo(OrdreFabricationLigne::class, 'of_ligne_id');
    }

    /**
     * @return BelongsTo<MatierePremiere, $this>
     */
    public function matiere(): BelongsTo
    {
        return $this->belongsTo(MatierePremiere::class, 'matiere_id');
    }
}
