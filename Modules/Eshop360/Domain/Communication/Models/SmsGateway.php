<?php

namespace Modules\Eshop360\Domain\Communication\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class SmsGateway extends Model
{
    use BelongsToChannel, BelongsToInstance, HasFactory;

    protected $morphClass = \Modules\Eshop360\Models\SmsGateway::class;

    protected $table = 'eshop_sms_gateways';

    protected $fillable = [
        'channel_id',
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
