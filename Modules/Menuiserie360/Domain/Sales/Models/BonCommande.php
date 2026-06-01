<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Sales\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Menuiserie360\Domain\Commercial\Models\Devis;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;

/**
 * P2-4 — Bon de commande menuiserie.
 *
 * @phpstan-type BonCommandeFactory \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Menuiserie360\Domain\Sales\Models\BonCommande>
 */
class BonCommande extends Model
{
    /** @use HasFactory<BonCommandeFactory> */
    use BelongsToInstance, HasFactory, SoftDeletes;

    protected $table = 'mnu_bon_commandes';

    protected $fillable = [
        'instance_id',
        'numero',
        'devis_id',
        'client_id',
        'chantier_id',
        'facture_acompte_id',
        'statut',
        'montant_ht',
        'taux_tva',
        'montant_tva',
        'montant_ttc',
        'acompte_pct',
        'date_livraison_prevue',
        'date_livraison_reelle',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'montant_ht' => 'decimal:2',
        'taux_tva' => 'decimal:4',
        'montant_tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
        'acompte_pct' => 'decimal:2',
        'date_livraison_prevue' => 'date',
        'date_livraison_reelle' => 'date',
    ];

    public function montantAcompte(): float
    {
        return round((float) $this->getAttribute('montant_ttc') * (float) $this->getAttribute('acompte_pct') / 100.0, 2);
    }

    /**
     * @return BelongsTo<Devis, $this>
     */
    public function devis(): BelongsTo
    {
        return $this->belongsTo(Devis::class, 'devis_id');
    }

    /**
     * @return BelongsTo<MenuiserieInvoice, $this>
     */
    public function factureAcompte(): BelongsTo
    {
        return $this->belongsTo(MenuiserieInvoice::class, 'facture_acompte_id');
    }

    /**
     * @return HasMany<BonCommandeItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(BonCommandeItem::class, 'bc_id');
    }
}
