<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class CronLog extends Model
{
    protected $table = 'cron_logs';

    protected $fillable = [
        'command',
        'status',
        'output',
        'duration_ms',
        'executed_at',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
        'duration_ms' => 'integer',
    ];

    /**
     * Scope: filter by command name.
     */
    public function scopeForCommand($query, ?string $command)
    {
        return $command ? $query->where('command', $command) : $query;
    }

    /**
     * Scope: filter by status.
     */
    public function scopeWithStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    /**
     * Scope: filter by date range.
     */
    public function scopeBetweenDates($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->where('executed_at', '>=', $from);
        }
        if ($to) {
            $query->where('executed_at', '<=', $to . ' 23:59:59');
        }
        return $query;
    }
}
