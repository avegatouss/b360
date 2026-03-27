<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Traits\BelongsToInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class PaymentMethod extends Model
{
    use HasFactory, BelongsToInstance, BelongsToChannel;

    protected $table = 'eshop_payment_methods';

    protected $fillable = [
        'channel_id',
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
