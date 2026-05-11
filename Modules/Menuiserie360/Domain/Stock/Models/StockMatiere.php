<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Stock\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Traits\BelongsToInstance;

/**
 * P1-2 — Niveau de stock par matière première.
 *
 * @phpstan-type StockMatiereFactory \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Menuiserie360\Domain\Stock\Models\StockMatiere>
 */
class StockMatiere extends Model
{
    /** @use HasFactory<StockMatiereFactory> */
    use BelongsToInstance, HasFactory;

    protected $table = 'mnu_stocks_matieres';

    protected $fillable = [
        'instance_id',
        'matiere_id',
        'quantite_actuelle',
        'quantite_reservee',
        'derniere_entree_at',
        'derniere_sortie_at',
    ];

    protected $casts = [
        'quantite_actuelle' => 'decimal:4',
        'quantite_reservee' => 'decimal:4',
        'derniere_entree_at' => 'datetime',
        'derniere_sortie_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<MatierePremiere, $this>
     */
    public function matiere(): BelongsTo
    {
        return $this->belongsTo(MatierePremiere::class, 'matiere_id');
    }

    public function quantiteDisponible(): float
    {
        return (float) $this->getAttribute('quantite_actuelle')
            - (float) $this->getAttribute('quantite_reservee');
    }
}
