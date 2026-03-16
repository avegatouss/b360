<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Database\Traits\BelongsToInstance;

class DistributionChannel extends Model
{
    use HasFactory, BelongsToInstance;

    protected $table = 'eshop_distribution_channels';

    protected $fillable = [
        'instance_id',
        'name',
        'slug',
        'code',
        'description',
        'is_active',
        'margin_rate',
        'buy_rate',
        'debt_share',
        'channel_share',
        'owner_share',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'margin_rate' => 'decimal:4',
        'buy_rate' => 'decimal:4',
        'debt_share' => 'decimal:4',
        'channel_share' => 'decimal:4',
        'owner_share' => 'decimal:4',
        'settings' => 'array',
    ];

    public function marginLogs(): HasMany
    {
        return $this->hasMany(ChannelMarginLog::class, 'channel_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'channel_id');
    }

    public function productPrices(): HasMany
    {
        return $this->hasMany(ChannelProductPrice::class, 'channel_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'eshop_channel_product_prices', 'channel_id', 'product_id')
            ->withPivot('sale_price', 'is_manual_override')
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Calculate the channel sale price for a given PGHT.
     */
    public function calculateSalePrice(float $pght): float
    {
        return round($pght * (1 + $this->buy_rate), 4);
    }
}
