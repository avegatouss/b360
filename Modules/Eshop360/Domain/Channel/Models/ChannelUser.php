<?php

namespace Modules\Eshop360\Domain\Channel\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChannelUser extends Model
{
    use HasFactory;

    protected $table = 'eshop_channel_users';

    protected $fillable = [
        'channel_id',
        'user_id',
        'role',
    ];

    protected $casts = [
        'role' => 'string',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(DistributionChannel::class, 'channel_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
