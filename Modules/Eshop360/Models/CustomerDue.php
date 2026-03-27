<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class CustomerDue extends Model
{
    use HasFactory, BelongsToChannel;

    protected $table = 'eshop_customer_dues';

    protected $fillable = [
        'channel_id',
        'customer_id',
        'order_id',
        'invoice_id',
        'amount_due',
        'paid_amount',
        'due_date',
        'status',
    ];

    protected $casts = [
        'amount_due'  => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date'    => 'date',
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
