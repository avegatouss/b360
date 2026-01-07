<?php

namespace App\Instances;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Instance B360
 *
 * Règles fondamentales :
 * - Toujours stockée dans la base "system"
 * - Ne contient AUCUNE donnée métier
 * - Sert uniquement à la résolution, au routage et au contexte
 */
class Instance extends Model
{
    /**
     * Connexion centrale obligatoire
     */
    protected $connection = 'system';

    /**
     * Table explicite
     */
    protected $table = 'instances';

    /**
     * Champs assignables
     */
    protected $fillable = [
        'name',
        'slug',

        // Résolution
        'domain',
        'subdomain',
        'path',

        // Database-per-instance
        'database',
        'db_driver',

        // État
        'is_active',
        'installed_at',

        // Métadonnées libres
        'meta',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'is_active'    => 'boolean',
        'installed_at' => 'datetime',
        'meta'         => 'array',
    ];

    /* -----------------------------------------------------------------
     |  Scopes
     |-----------------------------------------------------------------*/

    /**
     * Instances actives uniquement
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Instance ROOT (fallback)
     */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->where('slug', 'root');
    }

    /* -----------------------------------------------------------------
     |  Helpers de résolution
     |-----------------------------------------------------------------*/

    /**
     * Indique si l'instance est ROOT
     */
    public function isRoot(): bool
    {
        return ($this->meta['is_root'] ?? false) === true;
    }

    /**
     * Indique si l'instance possède une DB dédiée
     */
    public function hasDedicatedDatabase(): bool
    {
        return !empty($this->database);
    }

    /**
     * Retourne le nom de la connexion à utiliser pour le métier
     *
     * IMPORTANT :
     * - shared  → system
     * - database-per-instance → instance
     */
    public function getBusinessConnectionName(): string
    {
        return $this->hasDedicatedDatabase()
            ? 'instance'
            : 'system';
    }

    /* -----------------------------------------------------------------
     |  Guards de sécurité
     |-----------------------------------------------------------------*/

    /**
     * Empêche toute écriture si l'instance est inactive
     */
    protected static function booted(): void
    {
        static::updating(function (Instance $instance) {
            if ($instance->exists && $instance->is_active === false) {
                return true; // autorisé (ex: désactivation)
            }
            return true;
        });
    }
}
