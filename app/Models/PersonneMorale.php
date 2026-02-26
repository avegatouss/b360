<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PersonneMorale extends Model implements HasMedia
{
    use SoftDeletes, HasFactory, InteractsWithMedia;

    protected $fillable = [
        'personne_id',
        'raison_sociale',
        'sigle',
        'forme_societe',
        'secteur_activite',
        'nom_groupe',
        'logo',
        'rccm',
        'ncc',
        'num_identification_fiscale',
        'siege_social',
        'adresse_siege'
    ];

    public function personne()
    {
        return $this->belongsTo(Personne::class);
    }

    public function representants()
    {
        return $this->hasMany(Representant::class, 'personne_morale_id');
    }

    public function representantPrincipal()
    {
        return $this->representants()->where('est_principal', true)->first();
    }

    /**
     * Rôles de la personne liée (via personne_id partagé).
     */
    public function personneRoles()
    {
        return $this->hasMany(PersonneRole::class, 'personne_id', 'personne_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logos')->singleFile();
        $this->addMediaCollection('documents_statuts');
        $this->addMediaCollection('documents_autre');
    }
    /**
     * Scope pour les propriétaires
     */
    public function scopeProprietaires(Builder $query): Builder
    {
        return $query->whereHas('personneRoles', function (Builder $q) {
            $q->where('role', 'proprietaire');
        });
    }

    /**
     * Scope pour les locataires
     */
    public function scopeLocataires(Builder $query): Builder
    {
        return $query->whereHas('personneRoles', function (Builder $q) {
            $q->where('role', 'locataire');
        });
    }

    /**
     * Scope pour un rôle spécifique
     */
    public function scopeWithRole(Builder $query, string $role): Builder
    {
        return $query->whereHas('personneRoles', function (Builder $q) use ($role) {
            $q->where('role', $role);
        });
    }

    /**
     * Récupère le rôle actuel (le plus récent)
     */
    public function getRoleActuelAttribute(): ?string
    {
        $dernierRole = $this->personneRoles()->latest()->first();
        return $dernierRole ? $dernierRole->role : null;
    }

    /**
     * Récupère la référence du rôle actuel
     */
    public function getReferenceRoleActuelAttribute(): ?string
    {
        $dernierRole = $this->personneRoles()->latest()->first();
        return $dernierRole ? $dernierRole->reference_role : null;
    }

    /**
     * Vérifie si la personne est propriétaire
     */
    public function isProprietaire(): bool
    {
        return $this->personneRoles()->where('role', 'proprietaire')->exists();
    }

    /**
     * Vérifie si la personne est locataire
     */
    public function isLocataire(): bool
    {
        return $this->personneRoles()->where('role', 'locataire')->exists();
    }

    /**
     * Ajoute un rôle à la personne
     */
    public function assignerRole(string $role, array $metadata = []): PersonneRole
    {
        $reference = app(\App\Services\GeneratorService::class)
            ->generateForPersonneRole($this->id, $role);

        return $this->personneRoles()->create([
            'role' => $role,
            'reference_role' => $reference,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Récupère l'historique des rôles
     */
    public function getHistoriqueRolesAttribute()
    {
        return $this->personneRoles()->orderByDesc('created_at')->get();
    }
}
