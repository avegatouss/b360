<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $connection = 'system';
    protected $table = 'billing_webhook_logs';

    protected $fillable = [
        'gateway_slug',
        'instance_id',
        'event_type',
        'payload',
        'result',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'result' => 'array',
        'processed_at' => 'datetime',
    ];
}
