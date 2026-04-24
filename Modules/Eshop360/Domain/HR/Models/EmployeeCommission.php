<?php

namespace Modules\Eshop360\Domain\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Eshop360\Database\Traits\BelongsToChannel;
use Modules\Eshop360\Models\Order;

class EmployeeCommission extends Model
{
    use BelongsToChannel, HasFactory;

    protected $morphClass = \Modules\Eshop360\Models\EmployeeCommission::class;

    protected $table = 'eshop_employee_commissions';

    protected $fillable = [
        'channel_id',
        'employee_id',
        'order_id',
        'rate',
        'amount',
        'paid_at',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'paid_at' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
