<?php

namespace Modules\Currency\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Immutable currency snapshot attached to an order, invoice, or payment.
 * NEVER update after creation — the rate is frozen at the moment of the transaction.
 */
class OrderCurrencySnapshot extends Model
{
    public $timestamps = false;

    protected $table = 'order_currency_snapshots';

    protected $fillable = [
        'snapshotable_type',
        'snapshotable_id',
        'display_currency',
        'base_currency',
        'exchange_rate',
        'amounts',
        'snapshotted_at',
    ];

    protected $casts = [
        'exchange_rate' => 'float',
        'amounts' => 'array',
        'snapshotted_at' => 'datetime',
    ];

    public function snapshotable(): MorphTo
    {
        return $this->morphTo();
    }
}
