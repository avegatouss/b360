<?php

namespace Modules\Eshop360\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Eshop360\Database\Traits\BelongsToChannel;
use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Eshop360\Domain\Sales\Models\Order;

class InstallmentPlan extends Model
{
    use BelongsToChannel, BelongsToInstance, HasFactory;

    protected $table = 'eshop_installment_plans';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'order_id',
        'total',
        'installments_count',
        'frequency',
        'status',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InstallmentPayment::class, 'plan_id');
    }

    public function installments(): HasMany
    {
        return $this->payments();
    }

    public function getReferenceAttribute(): string
    {
        return sprintf('INST-%06d', $this->id ?? 0);
    }

    public function getCustomerAttribute(): ?Customer
    {
        return $this->order?->customer;
    }

    public function getTotalAmountAttribute(): float
    {
        return (float) $this->total;
    }

    public function getPaidAmountAttribute(): float
    {
        if ($this->relationLoaded('payments')) {
            return (float) $this->payments
                ->where('status', 'paid')
                ->sum('amount');
        }

        return (float) $this->payments()
            ->where('status', 'paid')
            ->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->total - $this->paid_amount);
    }

    public function getPaidInstallmentsCountAttribute(): int
    {
        if ($this->relationLoaded('payments')) {
            return (int) $this->payments
                ->where('status', 'paid')
                ->count();
        }

        return (int) $this->payments()
            ->where('status', 'paid')
            ->count();
    }

    public function getTotalInstallmentsCountAttribute(): int
    {
        return (int) $this->installments_count;
    }

    public function getNextDueDateAttribute(): mixed
    {
        $nextPayment = $this->relationLoaded('payments')
            ? $this->payments
                ->whereIn('status', ['pending', 'overdue'])
                ->sortBy('due_date')
                ->first()
            : $this->payments()
                ->whereIn('status', ['pending', 'overdue'])
                ->orderBy('due_date')
                ->first();

        return $nextPayment?->due_date;
    }
}
