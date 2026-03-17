<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;

class SmsGateway extends Model
{
    use HasFactory, BelongsToInstance;

    protected $table = 'eshop_sms_gateways';

    protected $fillable = [
        'instance_id',
        'driver',
        'display_name',
        'config',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'config' => 'encrypted:array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(SmsLog::class, 'gateway_id');
    }
}
