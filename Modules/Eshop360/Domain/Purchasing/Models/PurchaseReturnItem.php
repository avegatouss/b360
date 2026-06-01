<?php

namespace Modules\Eshop360\Domain\Purchasing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Eshop360\Database\Traits\BelongsToChannel;
use Modules\Eshop360\Domain\Catalog\Models\Product;

class PurchaseReturnItem extends Model
{
    use BelongsToChannel, HasFactory;

    protected $table = 'eshop_purchase_return_items';

    protected $fillable = [
        'channel_id',
        'purchase_return_id',
        'product_id',
        'quantity',
        'unit_cost',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
