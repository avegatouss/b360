<?php

namespace Modules\Eshop360\Domain\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Database\Traits\BelongsToInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class Payment extends Model
{
    use BelongsToChannel, BelongsToInstance, HasFactory;

    protected $table = 'eshop_payments';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'payable_type',
        'payable_id',
        'amount',
        'method',
        'gateway',
        'reference',
        'gateway_reference',
        'status',
        'notes',
        'metadata',
        'received_by',
        // Multi-currency snapshot (Currency module phase 2)
        'currency_code',
        'exchange_rate',
        'amount_in_base_currency',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        // Multi-currency: high-precision decimal for cross-currency reporting
        'exchange_rate' => 'decimal:10',
        'amount_in_base_currency' => 'decimal:4',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
