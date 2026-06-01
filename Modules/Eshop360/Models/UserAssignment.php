<?php

namespace Modules\Eshop360\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class UserAssignment extends Model
{
    use BelongsToChannel;

    protected $table = 'eshop_user_assignments';

    protected $fillable = [
        'channel_id',
        'user_id',
        'resource_type',
        'resource_id',
        'instance_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeStores(Builder $query): Builder
    {
        return $query->where('resource_type', 'store');
    }

    public function scopeWarehouses(Builder $query): Builder
    {
        return $query->where('resource_type', 'warehouse');
    }

    public function scopeCustomers(Builder $query): Builder
    {
        return $query->where('resource_type', 'customer');
    }

    public function scopeForInstance(Builder $query, int $instanceId): Builder
    {
        return $query->where('instance_id', $instanceId);
    }
}
