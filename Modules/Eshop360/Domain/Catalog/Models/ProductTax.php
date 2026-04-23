<?php

namespace Modules\Eshop360\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class ProductTax extends Model
{
    use BelongsToChannel, HasFactory;

    protected $table = 'eshop_product_taxes';

    protected $fillable = [
        'channel_id',
        'product_id',
        'tax_id',
        'type',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }
}
