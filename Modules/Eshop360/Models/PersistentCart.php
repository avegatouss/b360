<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Model;

class PersistentCart extends Model
{
    protected $table = 'eshop_carts';

    protected $fillable = [
        'instance_id',
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
