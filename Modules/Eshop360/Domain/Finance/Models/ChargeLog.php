<?php

namespace Modules\Eshop360\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class ChargeLog extends Model
{
    use BelongsToChannel, HasFactory;

    protected $table = 'eshop_charge_logs';

    protected $fillable = [
        'channel_id',
        'charge_id',
        'amount_per_second',
        'period_start',
        'period_end',
        'total_accumulated',
    ];

    protected $casts = [
        'amount_per_second' => 'decimal:10',
        'total_accumulated' => 'decimal:2',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
    ];

    public function charge(): BelongsTo
    {
        return $this->belongsTo(CompanyCharge::class, 'charge_id');
    }
}
