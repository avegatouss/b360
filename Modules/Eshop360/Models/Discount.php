<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Traits\BelongsToInstance;

class Discount extends Model
{
    use HasFactory, BelongsToInstance;

    protected $table = 'eshop_discounts';

    protected $fillable = [
        'instance_id',
        'name',
        'type',
        'value',
        'plan_type',
        'valid_from',
        'valid_until',
        'active_days',
        'applies_to',
        'product_ids',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'active_days' => 'array',
        'product_ids' => 'array',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];
}
