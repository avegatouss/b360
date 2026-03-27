<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Eshop360\Database\Traits\ScopedByUserAssignment;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class Stock extends Model
{
    use HasFactory, BelongsToInstance, ScopedByUserAssignment, BelongsToChannel;

    protected static array $userAssignmentConfig = [
        ['type' => 'warehouse', 'column' => 'warehouse_id'],
        ['type' => 'store', 'column' => 'store_id'],
    ];

    protected $table = 'eshop_stocks';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'product_id',
        'warehouse_id',
        'store_id',
        'quantity',
        'reserved_quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    protected function availableQuantity(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->quantity - $this->reserved_quantity,
        );
    }
}
