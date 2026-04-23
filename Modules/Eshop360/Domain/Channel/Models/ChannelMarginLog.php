<?php

namespace Modules\Eshop360\Domain\Channel\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Traits\BelongsToInstance;
// R-101 S3 — Order référencé via alias (FQN canon au S8).
use Modules\Eshop360\Models\Order;

class ChannelMarginLog extends Model
{
    use BelongsToInstance, HasFactory;

    protected $table = 'eshop_channel_margin_logs';

    protected $fillable = [
        'instance_id',
        'channel_id',
        'order_id',
        'total_margin',
        'debt_part',
        'channel_part',
        'owner_part',
    ];

    protected $casts = [
        'total_margin' => 'decimal:2',
        'debt_part' => 'decimal:2',
        'channel_part' => 'decimal:2',
        'owner_part' => 'decimal:2',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(DistributionChannel::class, 'channel_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getMarginAttribute(): float
    {
        return (float) ($this->total_margin ?? 0);
    }
}
