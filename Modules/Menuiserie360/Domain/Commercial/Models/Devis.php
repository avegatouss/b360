<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Commercial\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Database\Traits\BelongsToInstance;

/**
 * P1-6 — Devis menuiserie aluminium (chapeau).
 *
 * Workflow : brouillon → soumis → validé → accepté/refusé → transformé.
 *
 * @phpstan-type DevisFactory \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Menuiserie360\Domain\Commercial\Models\Devis>
 */
class Devis extends Model
{
    /** @use HasFactory<DevisFactory> */
    use BelongsToInstance, HasFactory, SoftDeletes;

    protected $table = 'mnu_devis';

    protected $fillable = [
        'instance_id',
        'numero',
        'client_id',
        'statut',
        'montant_ht',
        'taux_tva',
        'montant_tva',
        'montant_ttc',
        'remise_globale',
        'marge_minimum',
        'validite_jours',
        'date_validite',
        'conditions',
        'notes_internes',
        'created_by',
        'valide_par',
        'valide_at',
    ];

    protected $casts = [
        'montant_ht' => 'decimal:2',
        'taux_tva' => 'decimal:4',
        'montant_tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
        'remise_globale' => 'decimal:2',
        'marge_minimum' => 'decimal:4',
        'validite_jours' => 'integer',
        'date_validite' => 'date',
        'valide_at' => 'datetime',
    ];

    /**
     * @return HasMany<LigneDevis, $this>
     */
    public function lignes(): HasMany
    {
        return $this->hasMany(LigneDevis::class, 'devis_id');
    }
}
