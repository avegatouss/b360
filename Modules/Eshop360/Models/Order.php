<?php

namespace Modules\Eshop360\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Eshop360\Database\Scopes\OrderUserAssignmentScope;

class Order extends Model
{
    use HasFactory, BelongsToInstance, SoftDeletes;

    protected static function booted(): void
    {
        static::addGlobalScope(new OrderUserAssignmentScope());
    }

    protected $table = 'eshop_orders';

    protected $fillable = [
        'instance_id',
        'customer_id',
        'order_number',
        'status',
        'payment_status',
        'payment_method',
        'store_id',
        'warehouse_id',
        'cash_register_id',
        'holding_id',
        'channel_id',
        'payment_terms',
        'delivery_date',
        'delivered_at',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_amount',
        'total',
        'paid_amount',
        'due_amount',
        'coupon_code',
        'notes',
        'source',
        'biller_id',
        'employee_id',
        'project_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'delivery_date' => 'date',
        'delivered_at' => 'datetime',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function biller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'biller_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'biller_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'biller_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function holding(): BelongsTo
    {
        return $this->belongsTo(Holding::class);
    }

    public function installmentPlan(): HasOne
    {
        return $this->hasOne(InstallmentPlan::class);
    }

    public function employeeCommissions(): HasMany
    {
        return $this->hasMany(EmployeeCommission::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(DistributionChannel::class, 'channel_id');
    }

    public function channelMarginLogs(): HasMany
    {
        return $this->hasMany(ChannelMarginLog::class);
    }

    /**
     * Check if this order is associated with a distribution channel.
     */
    public function isChannelOrder(): bool
    {
        return $this->channel_id !== null;
    }

    /**
     * Scope to filter orders by distribution channel.
     */
    public function scopeForChannel(Builder $query, int $channelId): Builder
    {
        return $query->where('channel_id', $channelId);
    }

    public function getReferenceAttribute(): string
    {
        return (string) ($this->order_number ?? ('ORD-' . ($this->id ?? 'NEW')));
    }
}
