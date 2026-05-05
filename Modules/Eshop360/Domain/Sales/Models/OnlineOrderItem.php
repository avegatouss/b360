<?php

namespace Modules\Eshop360\Domain\Sales\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Eshop360\Database\Traits\BelongsToChannel;
use Modules\Eshop360\Domain\Catalog\Models\Product;

class OnlineOrderItem extends Model
{
    use BelongsToChannel, HasFactory;

    protected $table = 'eshop_online_order_items';

    protected $fillable = [
        'channel_id',
        'online_order_id',
        'product_id',
        'quantity',
        'unit_price',
        'total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function onlineOrder(): BelongsTo
    {
        return $this->belongsTo(OnlineOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
