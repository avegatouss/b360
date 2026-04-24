<?php

namespace Modules\Eshop360\Domain\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Database\Traits\BelongsToInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class Loan extends Model
{
    use BelongsToChannel, BelongsToInstance, HasFactory;

    protected $morphClass = \Modules\Eshop360\Models\Loan::class;

    protected $table = 'eshop_loans';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'reference',
        'type',
        'party_type',
        'party_id',
        'party_name',
        'amount',
        'interest_rate',
        'duration_months',
        'start_date',
        'due_date',
        'account_id',
        'paid_amount',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    public function party(): MorphTo
    {
        return $this->morphTo();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(LoanSchedule::class)->orderBy('installment_number');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getRemainingAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }

    public function getTotalWithInterestAttribute(): float
    {
        $interest = (float) $this->amount * ((float) $this->interest_rate / 100) * ((int) $this->duration_months / 12);

        return (float) $this->amount + $interest;
    }

    public function getPartyDisplayNameAttribute(): string
    {
        if ($this->party_name) {
            return $this->party_name;
        }
        if ($this->party) {
            return $this->party->name ?? $this->party->full_name ?? '—';
        }

        return '—';
    }
}
