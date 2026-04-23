<?php

namespace Modules\Eshop360\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class ProductVariation extends Model
{
    use BelongsToChannel, HasFactory;

    protected $table = 'eshop_product_variations';

    protected $fillable = [
        'channel_id',
        'product_id',
        'name',
        'sku',
        'barcode',
        'price',
        'cost_price',
        'quantity',
        'values',
        'image',
        'is_active',
    ];

    protected $casts = [
        'values' => 'array',
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the effective price (variation price or fallback to product price).
     */
    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->price ?? $this->product?->price ?? 0);
    }

    /**
     * Get the effective SKU (variation sku or fallback to product sku).
     */
    public function getEffectiveSkuAttribute(): string
    {
        return $this->sku ?: ($this->product?->sku ?? '');
    }

    /**
     * Get a display label like "Product Name - Variation Name".
     */
    public function getDisplayNameAttribute(): string
    {
        return ($this->product?->name ?? '').' - '.$this->name;
    }

    /**
     * Format the values array as a readable string (e.g., "Couleur: Rouge, Taille: L").
     */
    public function getValuesLabelAttribute(): string
    {
        if (empty($this->values)) {
            return $this->name;
        }

        return collect($this->values)
            ->map(fn ($value, $key) => $key.': '.$value)
            ->implode(', ');
    }
}
