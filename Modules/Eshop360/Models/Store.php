<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Eshop360\Database\Traits\ScopedByUserAssignment;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class Store extends Model
{
    use HasFactory, BelongsToInstance, ScopedByUserAssignment, BelongsToChannel;

    protected static array $userAssignmentConfig = [
        ['type' => 'store', 'column' => 'id'],
        ['type' => 'warehouse', 'column' => 'warehouse_id'],
    ];

    protected $table = 'eshop_stores';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'warehouse_id',
        'name',
        'code',
        'address',
        'phone',
        'email',
        'manager_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
