<?php

namespace Modules\Eshop360\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class LoanSchedule extends Model
{
    use BelongsToChannel;

    protected $table = 'eshop_loan_schedules';

    protected $fillable = [
        'channel_id',
        'loan_id',
        'installment_number',
        'due_date',
        'principal',
        'interest',
        'total_due',
        'paid_amount',
        'status',
    ];

    protected $casts = [
        'principal' => 'decimal:2',
        'interest' => 'decimal:2',
        'total_due' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function getRemainingAttribute(): float
    {
        return max(0, (float) $this->total_due - (float) $this->paid_amount);
    }
}
