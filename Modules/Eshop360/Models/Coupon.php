<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Traits\BelongsToInstance;

class Coupon extends Model
{
    use HasFactory, BelongsToInstance;

    protected $table = 'eshop_coupons';

    protected $fillable = [
        'instance_id',
        'channel_id',
        'name',
        'code',
        'description',
        'type',
        'value',
        'usage_limit',
        'used_count',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(DistributionChannel::class, 'channel_id');
    }

    /**
     * Scope: coupons visible inside a channel.
     * Channel coupons are isolated from Saphir Plus coupons by default.
     */
    public function scopeVisibleToChannel(Builder $query, ?int $channelId): Builder
    {
        if ($channelId) {
            return $query->where('channel_id', $channelId);
        }

        return $query->whereNull('channel_id');
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                  ->orWhereColumn('used_count', '<', 'usage_limit');
            });
    }
}
