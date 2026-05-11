<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Commercial\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Traits\BelongsToInstance;

/**
 * P1-6 — Ligne de devis dimensionnée.
 *
 * @phpstan-type LigneDevisFactory \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Menuiserie360\Domain\Commercial\Models\LigneDevis>
 */
class LigneDevis extends Model
{
    /** @use HasFactory<LigneDevisFactory> */
    use BelongsToInstance, HasFactory;

    protected $table = 'mnu_lignes_devis';

    protected $fillable = [
        'instance_id',
        'devis_id',
        'matiere_id',
        'designation',
        'quantite',
        'largeur_mm',
        'hauteur_mm',
        'prix_unitaire_ht',
        'remise_ligne',
        'montant_ht',
        'cout_revient',
        'ordre',
    ];

    protected $casts = [
        'quantite' => 'integer',
        'largeur_mm' => 'integer',
        'hauteur_mm' => 'integer',
        'prix_unitaire_ht' => 'decimal:4',
        'remise_ligne' => 'decimal:2',
        'montant_ht' => 'decimal:2',
        'cout_revient' => 'decimal:2',
        'ordre' => 'integer',
    ];

    /**
     * @return BelongsTo<Devis, $this>
     */
    public function devis(): BelongsTo
    {
        return $this->belongsTo(Devis::class, 'devis_id');
    }

    /**
     * Surface en m² calculée depuis les dimensions (utile pour vitrages).
     */
    public function surfaceM2(): ?float
    {
        $l = $this->getAttribute('largeur_mm');
        $h = $this->getAttribute('hauteur_mm');

        if ($l === null || $h === null) {
            return null;
        }

        return ((int) $l / 1000.0) * ((int) $h / 1000.0);
    }

    /**
     * Périmètre en mètres linéaires (utile pour profilés alu).
     */
    public function perimetreLineaire(): ?float
    {
        $l = $this->getAttribute('largeur_mm');
        $h = $this->getAttribute('hauteur_mm');

        if ($l === null || $h === null) {
            return null;
        }

        return 2.0 * (((int) $l / 1000.0) + ((int) $h / 1000.0));
    }
}
