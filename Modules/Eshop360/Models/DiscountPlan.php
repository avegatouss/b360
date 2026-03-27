<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Traits\BelongsToInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class DiscountPlan extends Model
{
    use HasFactory, BelongsToInstance, BelongsToChannel;

    protected $table = 'eshop_discount_plans';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'name',
        'target_segment',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
