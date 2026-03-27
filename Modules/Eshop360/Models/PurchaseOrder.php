<?php

namespace Modules\Eshop360\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Database\Traits\BelongsToInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class PurchaseOrder extends Model
{
    use HasFactory, BelongsToInstance, SoftDeletes, BelongsToChannel;

    protected $table = 'eshop_purchase_orders';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'supplier_id',
        'supplier_name',
        'supplier_email',
        'reference',
        'warehouse_id',
        'status',
        'total',
        'paid_amount',
        'due_amount',
        'payment_status',
        'notes',
        'received_at',
        'created_by',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'received_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getSupplierInfoAttribute(): ?object
    {
        // Use the proper supplier() BelongsTo relation when supplier_id is set.
        // This fallback object is only for backward-compat with legacy records that
        // only have supplier_name/email but no supplier_id FK.
        if ($this->supplier_id) {
            return null; // relation takes precedence
        }
        if (!$this->supplier_name && !$this->supplier_email) {
            return null;
        }

        return (object) [
            'name' => $this->supplier_name,
            'email' => $this->supplier_email,
        ];
    }
}
