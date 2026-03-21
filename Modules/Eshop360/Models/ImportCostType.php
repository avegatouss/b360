<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Traits\BelongsToInstance;

class ImportCostType extends Model
{
    use BelongsToInstance;

    protected $table = 'eshop_import_cost_types';

    protected $fillable = ['instance_id', 'code', 'label', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer'];

    public static function getForInstance(int $instanceId): \Illuminate\Support\Collection
    {
        return static::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();
    }
}
