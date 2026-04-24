<?php

namespace Modules\Eshop360\Domain\Purchasing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Eshop360\Database\Traits\BelongsToChannel;
use Modules\Eshop360\Models\Product;

class PurchaseItem extends Model
{
    use BelongsToChannel, HasFactory;

    protected $table = 'eshop_purchase_items';

    protected $fillable = [
        'channel_id',
        'purchase_order_id',
        'product_id',
        'quantity',
        'received_qty',
        'unit_cost',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'received_qty' => 'integer',
        'unit_cost' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
