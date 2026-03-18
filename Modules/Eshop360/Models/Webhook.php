<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;

class Webhook extends Model
{
    use BelongsToInstance;

    protected $table = 'eshop_webhooks';

    protected $fillable = [
        'instance_id',
        'name',
        'url',
        'secret',
        'events',
        'is_active',
        'failure_count',
        'last_triggered_at',
        'last_failed_at',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
        'failure_count' => 'integer',
        'last_triggered_at' => 'datetime',
        'last_failed_at' => 'datetime',
    ];

    protected $hidden = ['secret'];

    public function logs(): HasMany
    {
        return $this->hasMany(WebhookLog::class);
    }

    /**
     * Check if this webhook is subscribed to a given event.
     */
    public function subscribedTo(string $event): bool
    {
        return in_array($event, $this->events ?? [], true)
            || in_array('*', $this->events ?? [], true);
    }
}
