<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Traits\BelongsToInstance;

class PaymentMethod extends Model
{
    use HasFactory, BelongsToInstance;

    protected $table = 'eshop_payment_methods';

    protected $fillable = [
        'instance_id',
        'name',
        'type',
        'config',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
    ];
}
