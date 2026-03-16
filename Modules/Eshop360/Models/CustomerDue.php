<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerDue extends Model
{
    use HasFactory;

    protected $table = 'eshop_customer_dues';

    protected $fillable = [
        'customer_id',
        'order_id',
        'invoice_id',
        'amount_due',
        'due_date',
        'status',
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
