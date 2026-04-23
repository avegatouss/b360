<?php

namespace Modules\Eshop360\Domain\Channel\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;
// R-101 S3 — relations vers modèles hors-Channel : alias transitoires,
// remplacés par FQN canoniques au fil des sous-lots (Warehouse/Order/
// CashRegister → S5/S8, Coupon → S6, Holding → S9).
use Modules\Eshop360\Models\CashRegister;
use Modules\Eshop360\Models\Coupon;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Holding;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Product;
use Modules\Eshop360\Models\Warehouse;

class DistributionChannel extends Model
{
    use BelongsToInstance, HasFactory;

    protected $table = 'eshop_distribution_channels';

    protected $fillable = [
        'instance_id',
        'name',
        'slug',
        'code',
        'description',
        'is_active',
        'is_hub',
        'margin_rate',
        'buy_rate',
        'debt_share',
        'channel_share',
        'owner_share',
        'settings',
        'warehouse_id',
        'portal_enabled',
        'portal_settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_hub' => 'boolean',
        'margin_rate' => 'decimal:4',
        'buy_rate' => 'decimal:4',
        'debt_share' => 'decimal:4',
        'channel_share' => 'decimal:4',
        'owner_share' => 'decimal:4',
        'settings' => 'array',
        'portal_enabled' => 'boolean',
        'portal_settings' => 'array',
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
        // Bypass ChannelScope: the pivot table already provides the channel→product mapping.
        // Products are hub-level (channel_id = null) and linked to channels via the pivot.
        return $this->belongsToMany(Product::class, 'eshop_channel_product_prices', 'channel_id', 'product_id')
            ->withoutGlobalScope(\Modules\Eshop360\Database\Scopes\ChannelScope::class)
            ->withPivot('sale_price', 'is_manual_override')
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'eshop_channel_users', 'channel_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function channelUsers(): HasMany
    {
        return $this->hasMany(ChannelUser::class, 'channel_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get users with the 'manager' role for this channel.
     */
    public function managers(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'manager');
    }

    /**
     * Check whether the given user is a member of this channel.
     */
    public function isUserMember(int $userId): bool
    {
        return $this->channelUsers()->where('user_id', $userId)->exists();
    }

    /**
     * Calculate the channel sale price for a given PGHT.
     */
    public function calculateSalePrice(float $pght): float
    {
        return round($pght * (1 + $this->buy_rate), 4);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'channel_id');
    }

    public function cashRegisters(): HasMany
    {
        return $this->hasMany(CashRegister::class, 'channel_id');
    }

    public function holdings(): HasMany
    {
        return $this->hasMany(Holding::class, 'channel_id');
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class, 'channel_id');
    }
}
