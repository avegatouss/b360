<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Database\Traits\BelongsToInstance;

class Loan extends Model
{
    use HasFactory, BelongsToInstance;

    protected $table = 'eshop_loans';

    protected $fillable = [
        'instance_id',
        'party_type',
        'party_id',
        'amount',
        'interest_rate',
        'duration_months',
        'paid_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
    ];

    public function party(): MorphTo
    {
        return $this->morphTo();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function getRemainingAttribute(): float
    {
        return (float) $this->amount - (float) $this->paid_amount;
    }
}
