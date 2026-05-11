<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Chantier\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Traits\BelongsToInstance;

/**
 * P2-11 — Étape de chantier (jalon + avancement %).
 *
 * @phpstan-type EtapeFactory \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Menuiserie360\Domain\Chantier\Models\EtapeChantier>
 */
class EtapeChantier extends Model
{
    /** @use HasFactory<EtapeFactory> */
    use BelongsToInstance, HasFactory;

    protected $table = 'mnu_etapes_chantier';

    protected $fillable = [
        'instance_id',
        'chantier_id',
        'nom',
        'ordre',
        'avancement_pct',
        'statut',
        'demarree_at',
        'terminee_at',
        'notes',
    ];

    protected $casts = [
        'ordre' => 'integer',
        'avancement_pct' => 'integer',
        'demarree_at' => 'datetime',
        'terminee_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Chantier, $this>
     */
    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class, 'chantier_id');
    }
}
