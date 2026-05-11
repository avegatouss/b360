<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Chantier\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Menuiserie360\Domain\Sales\Models\BonCommande;

/**
 * P2-11 — Chantier de pose menuiserie.
 *
 * @phpstan-type ChantierFactory \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Menuiserie360\Domain\Chantier\Models\Chantier>
 */
class Chantier extends Model
{
    /** @use HasFactory<ChantierFactory> */
    use BelongsToInstance, HasFactory, SoftDeletes;

    protected $table = 'mnu_chantiers';

    protected $fillable = [
        'instance_id',
        'numero',
        'bc_id',
        'client_id',
        'statut',
        'adresse_pose',
        'contact_chantier',
        'date_debut_prevue',
        'date_debut_reelle',
        'date_fin_prevue',
        'date_fin_reelle',
        'chef_chantier_id',
        'equipe_user_ids',
        'notes',
    ];

    protected $casts = [
        'date_debut_prevue' => 'date',
        'date_debut_reelle' => 'date',
        'date_fin_prevue' => 'date',
        'date_fin_reelle' => 'date',
        'equipe_user_ids' => 'array',
    ];

    /**
     * @return BelongsTo<BonCommande, $this>
     */
    public function bonCommande(): BelongsTo
    {
        return $this->belongsTo(BonCommande::class, 'bc_id');
    }

    /**
     * @return HasMany<EtapeChantier, $this>
     */
    public function etapes(): HasMany
    {
        return $this->hasMany(EtapeChantier::class, 'chantier_id');
    }
}
