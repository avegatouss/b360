<?php

namespace Modules\Eshop360\Domain\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Eshop360\Database\Traits\BelongsToChannel;

class PersistentCart extends Model
{
    use BelongsToChannel;

    protected $table = 'eshop_carts';

    protected $fillable = [
        'instance_id',
        'channel_id',
        'user_id',
        'items',
        'coupon',
        'context',
        'expires_at',
    ];

    protected $casts = [
        'items' => 'array',
        'coupon' => 'array',
        'context' => 'array',
        'expires_at' => 'datetime',
    ];
}
