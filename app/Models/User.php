<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles, HasUuids;

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /*
     |--------------------------------------------------------------------------
     | Connection & Table
     |--------------------------------------------------------------------------
     */
    protected $connection = 'system';
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

        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /*
     |--------------------------------------------------------------------------
     | Hidden attributes (sécurité API / JSON)
     |--------------------------------------------------------------------------
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /*
     |--------------------------------------------------------------------------
     | Attribute Casting
     |--------------------------------------------------------------------------
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',

        'is_active' => 'boolean',
        'is_blocked' => 'boolean',

        'notification_preferences' => 'array',
        'settings' => 'array',

        'two_factor_secret' => 'encrypted',
        'two_factor_recovery_codes' => 'encrypted:array',
        'two_factor_confirmed_at' => 'datetime',
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

    /**
     * Resource assignments (eshop360 user-to-resource mapping).
     */
    public function resourceAssignments(): HasMany
    {
        return $this->hasMany(\Modules\Eshop360\Models\UserAssignment::class);
    }

    /*
     |--------------------------------------------------------------------------
     | Two-Factor Authentication (TOTP)
     |--------------------------------------------------------------------------
     | Requires: pragmarx/google2fa
     */

    /**
     * Check if 2FA is fully enabled (secret confirmed).
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_confirmed_at);
    }

    /**
     * Generate 8 random 10-character recovery codes.
     */
    public function generateTwoFactorRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = Str::random(10);
        }
        return $codes;
    }

    /**
     * Validate a TOTP code against the stored secret.
     */
    public function validTwoFactorCode(string $code): bool
    {
        if (empty($this->two_factor_secret)) {
            return false;
        }

        $google2fa = new Google2FA();

        return (bool) $google2fa->verifyKey($this->two_factor_secret, $code);
    }
}
