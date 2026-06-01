<?php

namespace Modules\Currency\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRateHistory extends Model
{
    public $timestamps = false;

    protected $table = 'exchange_rate_history';

    protected $fillable = [
        'base_code',
        'target_code',
        'rate',
        'source',
        'fetched_at',
    ];

    protected $casts = [
        'rate' => 'float',
        'fetched_at' => 'datetime',
    ];

    /**
     * Get the most recent rate for a currency pair.
     */
    public static function latestRate(string $baseCode, string $targetCode): ?float
    {
        $record = static::where('base_code', $baseCode)
            ->where('target_code', $targetCode)
            ->orderByDesc('fetched_at')
            ->first();

        return $record?->rate;
    }
}
