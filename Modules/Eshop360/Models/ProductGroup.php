<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Core\Database\Traits\BelongsToInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class ProductGroup extends Model
{
    use HasFactory, BelongsToInstance, BelongsToChannel;

    protected $table = 'eshop_product_groups';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'name',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'eshop_product_group_items');
    }
}
