<?php

namespace Modules\Eshop360\Domain\Catalog\Models;

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
// R-101 S1 — relations vers modèles hors-Catalog :
// on référence les alias `Modules\Eshop360\Models\*` pour transition fluide.
// Ils seront remplacés par leurs FQN canoniques au fur et à mesure des
// sous-lots suivants (ex. Stock → Domain\Inventory\Models\Stock après S5).
use Modules\Eshop360\Domain\Channel\Models\ChannelProductPrice;
use Modules\Eshop360\Domain\Channel\Models\DistributionChannel;
use Modules\Eshop360\Domain\Inventory\Models\Stock;
use Modules\Eshop360\Domain\Purchasing\Models\Supplier;
use Modules\Eshop360\Domain\Sales\Models\OrderItem;

class Product extends Model
{
    use BelongsToChannel, BelongsToInstance, HasFactory, SoftDeletes;

    protected $table = 'eshop_products';

    protected $fillable = [
        'instance_id',
        'channel_id',
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
        'wholesale_price_mode',
        'wholesale_price_rate',
        'pharmacy_price',
        'pharmacy_price_mode',
        'pharmacy_price_rate',
        'price_mode',
        'price_rate',
        'min_qty_wholesale',
        'min_order_quantity',
        'max_order_quantity',
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
        'wholesale_price_rate' => 'decimal:4',
        'pharmacy_price' => 'decimal:2',
        'pharmacy_price_rate' => 'decimal:4',
        'price_rate' => 'decimal:4',
        'min_qty_wholesale' => 'integer',
        'min_order_quantity' => 'integer',
        'max_order_quantity' => 'integer',
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
