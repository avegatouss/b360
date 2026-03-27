<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class OrderItem extends Model
{
    use HasFactory, BelongsToChannel;

    protected $table = 'eshop_order_items';

    protected $fillable = [
        'channel_id',
        'order_id',
        'product_id',
        'variation_id',
        'product_name',
        'variation_name',
        'sku',
        'quantity',
        'unit_price',
        'discount',
        'tax',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->product_name;
    }

    public function getTaxRateAttribute(): float
    {
        $baseAmount = (float) $this->unit_price * (int) $this->quantity;

        if ($baseAmount <= 0) {
            return 0.0;
        }

        return round(((float) $this->tax / $baseAmount) * 100, 2);
    }
}
