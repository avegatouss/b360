<?php

namespace Modules\Lang\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $table = 'translations';

    protected $fillable = [
        'instance_id',
        'locale',
        'group',
        'key',
        'value',
    ];

    public function scopeForLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    public function scopeForGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopeForInstance($query, int $instanceId)
    {
        return $query->where(function ($q) use ($instanceId) {
            $q->where('instance_id', $instanceId)
              ->orWhere('instance_id', 0);
        });
    }
}
