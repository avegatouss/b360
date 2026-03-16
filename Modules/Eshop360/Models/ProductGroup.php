<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Core\Database\Traits\BelongsToInstance;

class ProductGroup extends Model
{
    use HasFactory, BelongsToInstance;

    protected $table = 'eshop_product_groups';

    protected $fillable = [
        'instance_id',
        'name',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'eshop_product_group_items');
    }
}
