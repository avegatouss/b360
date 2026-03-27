<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class Attendance extends Model
{
    use HasFactory, BelongsToChannel;

    protected $table = 'eshop_attendance';

    protected $fillable = [
        'channel_id',
        'employee_id',
        'date',
        'clock_in',
        'clock_out',
        'hours_worked',
        'notes',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'hours_worked' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
