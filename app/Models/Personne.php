<?php
// app/Models/Personne.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Personne extends Model implements HasMedia
{
    use SoftDeletes, HasFactory, InteractsWithMedia, HasUuids;

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected $fillable = [
        'type',
        'identifiant',
        'email',
        'telephone',
        'nationalite',
        'pays',
        'actif',
        'origine_donnees'
    ];

    protected $casts = [
        'actif' => 'bool',
    ];

    /**
     * RELATIONS EXISTANTES
     */
    public function roles()
    {
        return $this->hasMany(PersonneRole::class);
    }

    public function personnePhysique()
    {
        return $this->hasOne(PersonnePhysique::class);
    }

    public function personneMorale()
    {
        return $this->hasOne(PersonneMorale::class);
    }

    public function user()
    {
        return $this->hasOne(User::class , 'personne_id');
    }

    public function representatives()
    {
        return $this->hasManyThrough(
            Representant::class ,
            PersonneMorale::class ,
            'personne_id',
            'personne_morale_id'
        );
    }

    /**
     * SCOPES
     */
    public function scopeSansRepresentants($query)
    {
        return $query->where('type', 'physique')
            ->whereDoesntHave('personnePhysique.representations');
    }
    /**
     * Scope pour les personnes avec plusieurs rôles
     */
    public function scopeMultiroles($query)
    {
        return $query->whereExists(function ($subQuery) {
            $subQuery->select(DB::raw(1))
                ->from('personne_roles')
                ->whereRaw('personnes.id = personne_roles.personne_id')
                ->groupBy('personne_roles.personne_id')
                ->havingRaw('COUNT(DISTINCT personne_roles.role) > 1');
        });
    }

    /**
     * Vérifie si la personne a plusieurs rôles
     */
    public function isMultirole(): bool
    {
        return $this->roles()
            ->selectRaw('COUNT(DISTINCT role) as role_count')
            ->groupBy('personne_id')
            ->value('role_count') > 1;
    }
    public static function nonRepresentants()
    {
        return static::query()
            ->where('type', 'physique')
            ->whereHas('personnePhysique', function ($q) {
            $q->whereDoesntHave('representations');
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeWithUserAccount($query)
    {
        return $query->whereHas('user');
    }

    /**
     * ATTRIBUTS CALCULÉS
     */
    public function getRolesListAttribute()
    {
        return $this->relationLoaded('roles')
            ? $this->roles->pluck('role')->unique()->implode(', ')
            : $this->roles()->pluck('role')->unique()->implode(', ');
    }

    public function getFullNameAttribute()
    {
        if ($this->isPhysique()) {
            $pp = $this->relationLoaded('personnePhysique') ? $this->personnePhysique : $this->personnePhysique()->first();
            return $pp ? $pp->nom_complet : null;
        }
        if ($this->isMoral()) {
            $pm = $this->relationLoaded('personneMorale') ? $this->personneMorale : $this->personneMorale()->first();
            return $pm ? $pm->raison_sociale : null;
        }
        return null;
    }

    public function getNomAffichageAttribute()
    {
        if ($this->isPhysique()) {
            $pp = $this->relationLoaded('personnePhysique') ? $this->personnePhysique : $this->personnePhysique()->first();
            return $pp ? $pp->nom_affichage : null;
        }
        if ($this->isMoral()) {
            $pm = $this->relationLoaded('personneMorale') ? $this->personneMorale : $this->personneMorale()->first();
            return $pm ? $pm->raison_sociale : null;
        }
        return null;
    }

    /**
     * MÉTHODES UTILITAIRES
     */

    // Vérifie si la personne a un rôle spécifique
    public function hasRole($role)
    {
        return $this->roles()->where('role', $role)->exists();
    }

    public function isPhysique(): bool
    {
        return $this->type === 'physique';
    }

    public function isMoral(): bool
    {
        return $this->type === 'moral';
    }

    public function hasUserAccount(): bool
    {
        return $this->user()->exists();
    }

    /**
     * Peut-on créer un compte pour cette personne ?
     */
    public function canHaveUserAccount(): bool
    {
        if ($this->isPhysique()) {
            return filled($this->email) || filled($this->telephone);
        }

        if ($this->isMoral()) {
            if (filled($this->email) || filled($this->telephone)) {
                return true;
            }
            $pm = $this->relationLoaded('personneMorale') ? $this->personneMorale : $this->personneMorale()->first();
            if (!$pm)
                return false;

            $rep = $pm->representantPrincipal();
            if (!$rep || !$rep->representant)
                return false;

            $repPersonne = $rep->representant->personne;
            return $repPersonne && (filled($repPersonne->email) || filled($repPersonne->telephone));
        }

        return false;
    }

    /**
     * Crée un User lié à la Personne
     */
    public function createUserAccount(array $data): User
    {
        $payload = array_merge($data, [
            'personne_id' => $this->id,
        ]);

        if ($this->isPhysique()) {
            $pp = $this->relationLoaded('personnePhysique') ? $this->personnePhysique : $this->personnePhysique()->first();
            if ($pp) {
                $payload['personne_physique_id'] = $pp->id;
                $payload['first_name'] = $payload['first_name'] ?? ($pp->prenom ?? null);
                $payload['last_name'] = $payload['last_name'] ?? ($pp->nom ?? null);
                $payload['full_name'] = $payload['full_name'] ?? trim(($payload['first_name'] ?? '') . ' ' . ($payload['last_name'] ?? ''));
            }
        }

        if (empty($payload['full_name'])) {
            $payload['full_name'] = $this->nom_affichage ?? ($this->email ?: 'User-' . $this->id);
        }

        return User::create($payload);
    }

    public function currentRole()
    {
        return $this->roles()->latest()->first();
    }
}
