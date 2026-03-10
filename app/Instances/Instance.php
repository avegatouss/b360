<?php

namespace App\Instances;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
    use HasUuids;

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected $connection = 'system';

    protected $table = 'instances';

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'subdomain',
        'path',
        'database',
        'db_driver',
        'is_active',
        'installed_at',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'installed_at' => 'datetime',
        'meta' => 'array',
    ];

    /* -----------------------------------------------------------------
     |  Scopes
     |-----------------------------------------------------------------*/

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->where('slug', 'root');
    }

    /* -----------------------------------------------------------------
     |  Helpers
     |-----------------------------------------------------------------*/

    public function isRoot(): bool
    {
        return ($this->meta['is_root'] ?? false) === true;
    }

    public function hasDedicatedDatabase(): bool
    {
        return !empty($this->database);
    }

    public function getBusinessConnectionName(): string
    {
        return $this->hasDedicatedDatabase()
            ? 'instance'
            : 'system';
    }

    /* -----------------------------------------------------------------
     |  Relations
     |-----------------------------------------------------------------*/

    public function subscription(): HasOne
    {
        return $this->hasOne(\Modules\Billing\Models\Subscription::class)->latestOfMany();
    }

    public function license(): HasOne
    {
        return $this->hasOne(\Modules\Core\Models\License::class);
    }

    /* -----------------------------------------------------------------
     |  Guards de sécurité
     |-----------------------------------------------------------------*/

    protected static function booted(): void
    {
        static::updating(function (Instance $instance) {
            // Toujours autoriser la mise à jour (y compris désactivation)
            return true;
        });
    }
}
