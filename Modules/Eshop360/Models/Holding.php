<?php

namespace Modules\Eshop360\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;

class Holding extends Model
{
    use HasFactory, BelongsToInstance;

    protected $table = 'eshop_holdings';

    protected $fillable = [
        'instance_id',
        'channel_id',
        'customer_id',
        'reference',
        'items',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(DistributionChannel::class, 'channel_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'holding_id');
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getCartLinesAttribute(): array
    {
        $items = $this->items ?? [];

        if (isset($items['lines']) && is_array($items['lines'])) {
            return $items['lines'];
        }

        return is_array($items) ? $items : [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getHeldCouponAttribute(): ?array
    {
        $items = $this->items ?? [];

        if (isset($items['coupon']) && is_array($items['coupon'])) {
            return $items['coupon'];
        }

        return null;
    }

    public function getItemsCountAttribute(): int
    {
        return count($this->cart_lines);
    }
}
