<?php

namespace Modules\Eshop360\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Database\Traits\BelongsToInstance;

class ImportOrder extends Model
{
    use HasFactory, BelongsToInstance, SoftDeletes;

    protected $table = 'eshop_import_orders';

    protected $fillable = [
        'instance_id',
        'supplier_id',
        'reference',
        'container_no',
        'shipping_type',
        'ship_date',
        'eta',
        'status',
        'warehouse_id',
        'cost_allocation_method',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'ship_date' => 'date',
        'eta' => 'date',
        'status' => 'string',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ImportOrderItem::class);
    }

    public function costs(): HasMany
    {
        return $this->hasMany(ImportCost::class);
    }

    public function getTotalFactoryAttribute(): float
    {
        return (float) $this->items()->sum('total_factory');
    }

    public function getTotalCostsAttribute(): float
    {
        return (float) $this->costs()->sum('amount');
    }

    public function getItemsTotalAttribute(): float
    {
        return $this->total_factory;
    }

    public function getCostsTotalAttribute(): float
    {
        return $this->total_costs;
    }

    public function getTotalAttribute(): float
    {
        return $this->total_factory + $this->total_costs;
    }

    public function getIsAllocatedAttribute(): bool
    {
        return $this->items()->where('allocated_cost', '>', 0)->exists();
    }
}
