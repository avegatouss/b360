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

    /**
     * Champs assignables.
     */
    protected $fillable = [
        'name',
        'slug',
        'database',
        'is_active',
        'meta',
    ];

    /**
     * Casts automatiques.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'meta'      => 'array',
    ];
}
