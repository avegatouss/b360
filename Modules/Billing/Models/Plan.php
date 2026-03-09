<?php

namespace Modules\Billing\Models;

use App\Instances\Instance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Plan extends Model
{
    protected $connection = 'system';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'trial_days',
        'features',
        'is_active',
        'visibility',
        'sort_order',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'trial_days' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Plan $plan) {
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function instances(): BelongsToMany
    {
        return $this->belongsToMany(Instance::class, 'plan_instance')
            ->withTimestamps();
    }

    public function scopeVisibleTo(Builder $query, int $instanceId): Builder
    {
        return $query->where(function (Builder $q) use ($instanceId) {
            $q->where('visibility', 'all')
              ->orWhere(function (Builder $q2) use ($instanceId) {
                  $q2->where('visibility', 'specific')
                     ->whereHas('instances', fn (Builder $q3) => $q3->where('instances.id', $instanceId));
              });
        });
    }
}
