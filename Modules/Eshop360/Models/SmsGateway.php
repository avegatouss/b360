<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Traits\BelongsToInstance;

class SmsGateway extends Model
{
    use HasFactory, BelongsToInstance;

    protected $table = 'eshop_sms_gateways';

    protected $fillable = [
        'instance_id',
        'provider',
        'config',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];
}
