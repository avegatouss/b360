<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Stock\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Traits\BelongsToInstance;

/**
 * P1-2 — Mouvement de stock matière (audit trail immuable).
 *
 * @phpstan-type MouvementStockFactory \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Menuiserie360\Domain\Stock\Models\MouvementStock>
 */
class MouvementStock extends Model
{
    /** @use HasFactory<MouvementStockFactory> */
    use BelongsToInstance, HasFactory;

    protected $table = 'mnu_mouvements_stock';

    protected $fillable = [
        'instance_id',
        'matiere_id',
        'type',
        'quantite',
        'quantite_apres',
        'reference',
        'motif',
        'effectue_par',
    ];

    protected $casts = [
        'quantite' => 'decimal:4',
        'quantite_apres' => 'decimal:4',
    ];

    /**
     * @return BelongsTo<MatierePremiere, $this>
     */
    public function matiere(): BelongsTo
    {
        return $this->belongsTo(MatierePremiere::class, 'matiere_id');
    }
}
