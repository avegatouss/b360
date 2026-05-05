<?php

namespace Modules\Eshop360\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class LoanPayment extends Model
{
    use BelongsToChannel, HasFactory;

    protected $table = 'eshop_loan_payments';

    protected $fillable = [
        'channel_id',
        'loan_id',
        'amount',
        'date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
