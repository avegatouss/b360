<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    public $timestamps = false;

    protected $table = 'eshop_webhook_logs';

    protected $fillable = [
        'webhook_id',
        'event',
        'response_code',
        'duration_ms',
        'payload',
        'response_body',
        'success',
        'created_at',
    ];

    protected $casts = [
        'success' => 'boolean',
        'response_code' => 'integer',
        'duration_ms' => 'integer',
        'created_at' => 'datetime',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }
}
