<?php

namespace Modules\Eshop360\Domain\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class EmployeeSalary extends Model
{
    use BelongsToChannel, HasFactory;

    protected $table = 'eshop_employee_salaries';

    protected $fillable = [
        'channel_id',
        'employee_id',
        'amount',
        'period',
        'bonus',
        'deductions',
        'net_amount',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'paid_at' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
