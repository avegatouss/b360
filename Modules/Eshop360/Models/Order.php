<?php

namespace Modules\Eshop360\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Database\Traits\BelongsToInstance;

class Order extends Model
{
    use HasFactory, BelongsToInstance, SoftDeletes;

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
        'is_codifarm',
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
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'is_codifarm' => 'boolean',
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

    public function channel(): BelongsTo
    {
        return $this->belongsTo(DistributionChannel::class, 'channel_id');
    }

    public function channelMarginLogs(): HasMany
    {
        return $this->hasMany(ChannelMarginLog::class);
    }

    public function codifarmMarginLog(): HasOne
    {
        return $this->hasOne(CodifarmMarginLog::class);
    }

    public function getReferenceAttribute(): string
    {
        return (string) ($this->order_number ?? ('ORD-' . ($this->id ?? 'NEW')));
    }
}
