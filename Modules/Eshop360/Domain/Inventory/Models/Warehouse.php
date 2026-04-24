<?php

namespace Modules\Eshop360\Domain\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Eshop360\Database\Traits\BelongsToChannel;
use Modules\Eshop360\Database\Traits\ScopedByUserAssignment;
use Modules\Eshop360\Models\Employee;

class Warehouse extends Model
{
    use BelongsToChannel, BelongsToInstance, HasFactory, ScopedByUserAssignment;

    protected static array $userAssignmentConfig = [
        ['type' => 'warehouse', 'column' => 'id'],
    ];

    protected $table = 'eshop_warehouses';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'name',
        'code',
        'address',
        'city',
        'phone',
        'email',
        'manager_name',
        'manager_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }
}
