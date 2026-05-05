<?php

namespace Modules\Eshop360\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class InstallmentPayment extends Model
{
    use BelongsToChannel, HasFactory;

    protected $table = 'eshop_installment_payments';

    protected $fillable = [
        'channel_id',
        'plan_id',
        'due_date',
        'amount',
        'paid_at',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(InstallmentPlan::class, 'plan_id');
    }

    public function installmentPlan(): BelongsTo
    {
        return $this->belongsTo(InstallmentPlan::class, 'plan_id');
    }

    public function getPaidAmountAttribute(): float
    {
        return $this->status === 'paid'
            ? (float) $this->amount
            : 0.0;
    }
}
