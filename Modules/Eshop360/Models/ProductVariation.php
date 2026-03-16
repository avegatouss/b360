<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariation extends Model
{
    use HasFactory;

    protected $table = 'eshop_product_variations';

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price',
        'cost_price',
        'quantity',
        'values',
    ];

    protected $casts = [
        'values' => 'array',
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
