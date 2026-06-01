<?php

namespace Modules\Currency\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = 'currencies';

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'decimals',
        'rate',
        'is_default',
        'is_active',
        'auto_update',
        'rate_updated_at',
    ];

    protected $casts = [
        'decimals' => 'integer',
        'rate' => 'float',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'auto_update' => 'boolean',
        'rate_updated_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
