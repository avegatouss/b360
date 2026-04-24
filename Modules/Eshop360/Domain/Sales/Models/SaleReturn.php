<?php

namespace Modules\Eshop360\Domain\Sales\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Eshop360\Database\Traits\BelongsToChannel;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Product;

class SaleReturn extends Model
{
    use BelongsToChannel, BelongsToInstance, HasFactory;

    protected $table = 'eshop_sale_returns';

    protected $morphClass = \Modules\Eshop360\Models\SaleReturn::class;

    protected $fillable = [
        'channel_id',
        'instance_id',
        'order_id',
        'customer_id',
        'product_id',
        'date',
        'status',
        'total',
        'paid_amount',
        'due_amount',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
