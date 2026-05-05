<?php

namespace Modules\Eshop360\Domain\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Eshop360\Database\Traits\BelongsToChannel;
use Modules\Eshop360\Domain\Catalog\Models\Product;

class StockTransferItem extends Model
{
    use BelongsToChannel, HasFactory;

    protected $table = 'eshop_stock_transfer_items';

    protected $fillable = [
        'channel_id',
        'stock_transfer_id',
        'product_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class, 'stock_transfer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
