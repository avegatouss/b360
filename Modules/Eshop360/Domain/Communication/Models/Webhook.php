<?php

namespace Modules\Eshop360\Domain\Communication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class Webhook extends Model
{
    use BelongsToChannel, BelongsToInstance;

    protected $table = 'eshop_webhooks';

    protected $fillable = [
        'channel_id',
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
