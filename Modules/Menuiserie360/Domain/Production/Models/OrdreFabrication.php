<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Production\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Menuiserie360\Domain\Sales\Models\BonCommande;

/**
 * P2-8 — Ordre de fabrication atelier.
 *
 * @phpstan-type OfFactory \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Menuiserie360\Domain\Production\Models\OrdreFabrication>
 */
class OrdreFabrication extends Model
{
    /** @use HasFactory<OfFactory> */
    use BelongsToInstance, HasFactory, SoftDeletes;

    protected $table = 'mnu_ordres_fabrication';

    protected $fillable = [
        'instance_id',
        'numero',
        'bc_id',
        'statut',
        'date_planifiee',
        'date_demarrage',
        'date_fin_reelle',
        'chef_atelier_id',
        'notes_atelier',
    ];

    protected $casts = [
        'date_planifiee' => 'date',
        'date_demarrage' => 'date',
        'date_fin_reelle' => 'date',
    ];

    /**
     * @return BelongsTo<BonCommande, $this>
     */
    public function bonCommande(): BelongsTo
    {
        return $this->belongsTo(BonCommande::class, 'bc_id');
    }

    /**
     * @return HasMany<OrdreFabricationLigne, $this>
     */
    public function lignes(): HasMany
    {
        return $this->hasMany(OrdreFabricationLigne::class, 'of_id');
    }
}
