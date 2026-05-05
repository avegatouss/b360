<?php

namespace Modules\Eshop360\Domain\Projects\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Traits\BelongsToInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class Event extends Model
{
    use BelongsToChannel, BelongsToInstance;

    protected $table = 'eshop_events';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'user_id',
        'title',
        'description',
        'start_at',
        'end_at',
        'all_day',
        'color',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'all_day' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
