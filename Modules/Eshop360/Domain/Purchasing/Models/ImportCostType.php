<?php

namespace Modules\Eshop360\Domain\Purchasing\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Eshop360\Database\Traits\BelongsToChannel;

class ImportCostType extends Model
{
    use BelongsToChannel, BelongsToInstance;

    protected $table = 'eshop_import_cost_types';

    protected $fillable = ['instance_id', 'channel_id', 'code', 'label', 'is_active', 'sort_order'];

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
