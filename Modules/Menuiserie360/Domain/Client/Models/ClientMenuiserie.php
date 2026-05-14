<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Client\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Menuiserie360\Domain\Client\Enums\StatutClientMenuiserie;

/**
 * Referentiel client natif Menuiserie360 (ADR-023).
 *
 * @phpstan-type ClientMenuiserieFactory \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Menuiserie360\Domain\Client\Models\ClientMenuiserie>
 */
class ClientMenuiserie extends Model
{
    /** @use HasFactory<ClientMenuiserieFactory> */
    use BelongsToInstance, HasFactory, SoftDeletes;

    protected $table = 'mnu_clients_menuiserie';

    protected $fillable = [
        'instance_id',
        'legacy_eshop_customer_id',
        'code',
        'type',
        'nom',
        'prenom',
        'raison_sociale',
        'email',
        'telephone_principal',
        'telephone_secondaire',
        'adresse',
        'ville',
        'pays',
        'rccm',
        'nif',
        'statut',
        'is_active',
        'preferred_contact_method',
        'total_chantiers_count',
        'total_revenue_xof',
        'notes_menuiserie',
    ];

    protected $casts = [
        'legacy_eshop_customer_id' => 'integer',
        'statut' => StatutClientMenuiserie::class,
        'is_active' => 'boolean',
        'total_chantiers_count' => 'integer',
        'total_revenue_xof' => 'decimal:2',
    ];

    public function getNameAttribute(): string
    {
        $raisonSociale = $this->getAttribute('raison_sociale');
        if (is_string($raisonSociale) && $raisonSociale !== '') {
            return $raisonSociale;
        }

        return trim(implode(' ', array_filter([
            $this->getAttribute('prenom'),
            $this->getAttribute('nom'),
        ], static fn ($value): bool => is_string($value) && $value !== ''))) ?: (string) $this->getAttribute('code');
    }
}
