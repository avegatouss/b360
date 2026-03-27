<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class PurchaseReturn extends Model
{
    use HasFactory, BelongsToInstance, BelongsToChannel;

    protected $table = 'eshop_purchase_returns';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'purchase_order_id',
        'supplier_name',
        'reference',
        'warehouse_id',
        'status',
        'total',
        'paid_amount',
        'due_amount',
        'payment_status',
        'notes',
        'created_by',
        'processed_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
