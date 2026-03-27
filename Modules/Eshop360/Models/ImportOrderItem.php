<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class ImportOrderItem extends Model
{
    use HasFactory, BelongsToChannel;

    protected $table = 'eshop_import_order_items';

    protected $fillable = [
        'channel_id',
        'import_order_id',
        'product_id',
        'quantity',
        'unit_price_factory',
        'total_factory',
        'allocated_cost',
        'cost_price_real',
    ];

    protected $casts = [
        'unit_price_factory' => 'decimal:4',
        'total_factory' => 'decimal:2',
        'allocated_cost' => 'decimal:2',
        'cost_price_real' => 'decimal:4',
    ];

    public function importOrder(): BelongsTo
    {
        return $this->belongsTo(ImportOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUnitCostAttribute(): float
    {
        return (float) $this->unit_price_factory;
    }

    public function getTotalAttribute(): float
    {
        return (float) $this->total_factory;
    }

    public function getLandedCostAttribute(): float
    {
        return (float) ($this->cost_price_real ?? $this->unit_price_factory);
    }
}
