<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class InvoiceItem extends Model
{
    use HasFactory, BelongsToChannel;

    protected $table = 'eshop_invoice_items';

    protected $fillable = [
        'channel_id',
        'invoice_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'discount',
        'tax',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getTaxRateAttribute(): float
    {
        $baseAmount = (float) $this->unit_price * (int) $this->quantity;

        if ($baseAmount <= 0) {
            return 0.0;
        }

        return round(((float) $this->tax / $baseAmount) * 100, 2);
    }
}
