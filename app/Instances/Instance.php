<?php

namespace App\Instances;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Instance
 *
 * Représente une instance B360.
 * Cette table est TOUJOURS dans la base centrale.
 */
class Instance extends Model
{
    /**
     * Table explicite pour éviter toute ambiguïté.
     */
    protected $table = 'instances';

    // IMPORTANT: la table instances est TOUJOURS sur la DB "system"
    protected $connection = 'system';

    /**
     * Champs assignables.
     */
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'database',
        'db_driver',
        'is_active',
        'meta',
        'installed_at',
    ];

    /**
     * Casts automatiques.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'meta'      => 'array',
        'installed_at' => 'datetime',
    ];
}
