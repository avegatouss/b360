<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */
    protected $table = 'users';

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    | ⚠️ Toujours explicite pour éviter les failles
    */
    protected $fillable = [
        'username',
        'email',
        'password',

        'first_name',
        'last_name',
        'full_name',
        'phone',
        'avatar',

        'is_active',
        'is_blocked',
        'last_login_at',

        'notification_preferences',
        'settings',

        'personne_id',
        'personne_physique_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Hidden attributes (sécurité API / JSON)
    |--------------------------------------------------------------------------
    */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Attribute Casting
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',

        'is_active'   => 'boolean',
        'is_blocked'  => 'boolean',

        'notification_preferences' => 'array',
        'settings'                 => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Accessors / Mutators
    |--------------------------------------------------------------------------
    */

    /**
     * Toujours stocker le mot de passe hashé
     */
    public function setPasswordAttribute(string $value): void
    {
        if (!empty($value)) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    /**
     * Nom complet automatique si absent
     */
    public function getFullNameAttribute(): ?string
    {
        if (!empty($this->attributes['full_name'])) {
            return $this->attributes['full_name'];
        }

        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')) ?: null;
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * Personne morale (ex: entreprise, organisation)
     */
    public function personne()
    {
        return $this->belongsTo(Personne::class);
    }

    /**
     * Personne physique (ex: individu)
     */
    public function personnePhysique()
    {
        return $this->belongsTo(PersonnePhysique::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Utilisateurs actifs uniquement
     */
    public function scopeActive($query)
    {
        return $query
            ->where('is_active', true)
            ->where('is_blocked', false);
    }

    /**
     * Utilisateurs bloqués
     */
    public function scopeBlocked($query)
    {
        return $query->where('is_blocked', true);
    }
    /*
    |--------------------------------------------------------------------------
    | Alias name ⇄ full_name
    |--------------------------------------------------------------------------
    */

    // Lire $user->name
    public function getNameAttribute(): ?string
    {
        return $this->full_name;
    }

    // Écrire $user->name = 'John Doe'
    public function setNameAttribute(?string $value): void
    {
        $this->attributes['full_name'] = $value;
    }
    /*
    |--------------------------------------------------------------------------
    | Helpers métier
    |--------------------------------------------------------------------------
    */

    public function block(): void
    {
        $this->update(['is_blocked' => true]);
    }

    public function unblock(): void
    {
        $this->update(['is_blocked' => false]);
    }

    public function markAsLoggedIn(): void
    {
        $this->update(['last_login_at' => now()]);
    }
}
