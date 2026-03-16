<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Traits\BelongsToInstance;

class DiscountPlan extends Model
{
    use HasFactory, BelongsToInstance;

    protected $table = 'eshop_discount_plans';

    protected $fillable = [
        'instance_id',
        'name',
        'target_segment',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
