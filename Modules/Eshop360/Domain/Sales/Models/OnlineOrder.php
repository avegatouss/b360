<?php

namespace Modules\Eshop360\Domain\Sales\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Eshop360\Database\Traits\BelongsToChannel;
use Modules\Eshop360\Models\Customer;

class OnlineOrder extends Model
{
    use BelongsToChannel, BelongsToInstance, HasFactory;

    protected $table = 'eshop_online_orders';

    protected $fillable = [
        'instance_id',
        'customer_id',
        'channel_id',
        'reference',
        'status',
        'subtotal',
        'tax_amount',
        'total',
        'delivery_address',
        'delivery_notes',
        'confirmed_at',
        'delivered_at',
        'received_at',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'delivered_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    /**
     * Check if this order is associated with a distribution channel.
     */
    public function isChannelOrder(): bool
    {
        return $this->channel_id !== null;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OnlineOrderItem::class);
    }
}
