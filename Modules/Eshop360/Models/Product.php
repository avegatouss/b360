<?php

namespace Modules\Eshop360\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Database\Traits\BelongsToInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class Product extends Model
{
    use HasFactory, BelongsToInstance, SoftDeletes, BelongsToChannel;

    protected $table = 'eshop_products';

    protected $fillable = [
        'instance_id',
        'category_id',
        'brand_id',
        'supplier_id',
        'name',
        'slug',
        'sku',
        'description',
        'price',
        'cost_price',
        'purchase_price_factory',
        'purchase_price_provisional',
        'pght',
        'cost_price_real',
        'tax_rate',
        'tax_inclusive',
        'discount_type',
        'discount_value',
        'unit',
        'min_quantity',
        'alert_quantity',
        'stock_alert_quantity',
        'expiry_alert_days',
        'barcode',
        'barcode_type',
        'qrcode',
        'image',
        'images',
        'location',
        'expiry_date',
        'manufactured_date',
        'batch_number',
        'dci',
        'dosage',
        'form',
        'packaging',
        'wholesale_price',
        'pharmacy_price',
        'min_qty_wholesale',
        'is_active',
        'selling_type',
        'created_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'purchase_price_factory' => 'decimal:4',
        'purchase_price_provisional' => 'decimal:4',
        'pght' => 'decimal:4',
        'cost_price_real' => 'decimal:4',
        'tax_rate' => 'decimal:2',
        'tax_inclusive' => 'boolean',
        'discount_value' => 'decimal:2',
        'stock_alert_quantity' => 'integer',
        'expiry_alert_days' => 'integer',
        'images' => 'array',
        'is_active' => 'boolean',
        'expiry_date' => 'date',
        'manufactured_date' => 'date',
        'wholesale_price' => 'decimal:2',
        'pharmacy_price' => 'decimal:2',
        'min_qty_wholesale' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereHas('stocks', function (Builder $q) {
            $q->whereColumn('eshop_stocks.quantity', '<=', 'eshop_products.alert_quantity');
        });
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now());
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays($days));
    }

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function productTaxes(): HasMany
    {
        return $this->hasMany(ProductTax::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(ProductGroup::class, 'eshop_product_group_items');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function channelPrices(): HasMany
    {
        return $this->hasMany(ChannelProductPrice::class);
    }

    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(DistributionChannel::class, 'eshop_channel_product_prices', 'product_id', 'channel_id')
            ->withPivot('sale_price', 'is_manual_override')
            ->withTimestamps();
    }

    /**
     * Get the sale price for a specific distribution channel, or null if not set.
     */
    public function priceForChannel(int $channelId): ?float
    {
        $channelPrice = $this->channelPrices()
            ->where('channel_id', $channelId)
            ->first();

        return $channelPrice ? (float) $channelPrice->sale_price : null;
    }
}
