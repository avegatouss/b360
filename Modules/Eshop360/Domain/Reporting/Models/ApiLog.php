<?php

namespace Modules\Eshop360\Domain\Reporting\Models;

use Illuminate\Database\Eloquent\Model;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class ApiLog extends Model
{
    use BelongsToChannel;

    protected $morphClass = \Modules\Eshop360\Models\ApiLog::class;

    protected $table = 'eshop_api_logs';

    protected $fillable = [
        'channel_id',
        'method',
        'endpoint',
        'response_code',
        'ip',
        'duration_ms',
        'user_id',
        'instance_id',
        'requested_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'duration_ms' => 'integer',
        'response_code' => 'integer',
    ];
}
