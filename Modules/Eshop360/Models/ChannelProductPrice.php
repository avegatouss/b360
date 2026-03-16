<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChannelProductPrice extends Model
{
    use HasFactory;

    protected $table = 'eshop_channel_product_prices';

    protected $fillable = [
        'channel_id',
        'product_id',
        'sale_price',
        'is_manual_override',
    ];

    protected $casts = [
        'sale_price' => 'decimal:4',
        'is_manual_override' => 'boolean',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(DistributionChannel::class, 'channel_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
