<?php

namespace Modules\Eshop360\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\ChannelUser;
use Modules\Eshop360\Models\DistributionChannel;

final class ChannelAccessService
{
    public function isHubAdmin(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->hasRole('super-admin') || $user->hasRole('instance-admin');
    }

    /**
     * Return null for hub admins, otherwise the list of accessible channels.
     *
     * @return Collection<int, int>|null
     */
    public function accessibleChannelIds(?User $user, ?int $instanceId = null): ?Collection
    {
        if (!$user) {
            return collect();
        }

        if ($this->isHubAdmin($user)) {
            return null;
        }

        $instanceId ??= CurrentInstance::get()?->id;

        return ChannelUser::query()
            ->where('user_id', $user->id)
            ->when($instanceId !== null, function ($query) use ($instanceId) {
                $query->whereHas('channel', function ($channelQuery) use ($instanceId) {
                    $channelQuery
                        ->where('instance_id', $instanceId)
                        ->where('is_active', true);
                });
            })
            ->pluck('channel_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    public function canAccessChannel(?User $user, int|DistributionChannel $channel, ?int $instanceId = null): bool
    {
        if (!$user) {
            return false;
        }

        if ($this->isHubAdmin($user)) {
            return true;
        }

        $channelId = $channel instanceof DistributionChannel ? $channel->id : $channel;
        $accessible = $this->accessibleChannelIds($user, $instanceId);

        return $accessible?->contains((int) $channelId) ?? false;
    }

    public function roleForChannel(?User $user, DistributionChannel $channel): ?string
    {
        if (!$user) {
            return null;
        }

        if ($this->isHubAdmin($user)) {
            return 'admin';
        }

        $role = $channel->channelUsers()
            ->where('user_id', $user->id)
            ->value('role');

        return $this->normalizeRole($role);
    }

    public function scopeToAccessibleChannels(
        Builder $query,
        ?User $user,
        string $channelColumn = 'channel_id',
        ?int $instanceId = null,
    ): Builder {
        if ($this->isHubAdmin($user)) {
            return $query;
        }

        $accessible = $this->accessibleChannelIds($user, $instanceId);

        if (!$accessible || $accessible->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($channelColumn, $accessible->all());
    }

    /**
     * Scope a query by channel access + optional channel filter.
     *
     * Hub admins see all channels by default, filtered to one if $filterChannelId given.
     * Channel users see only their channels, further filtered if $filterChannelId matches.
     *
     * Usage in controllers: $this->channelAccess->scopeWithChannelFilter($query, auth()->user(), $request->input('channel_id'));
     */
    public function scopeWithChannelFilter(
        Builder $query,
        ?User $user,
        ?int $filterChannelId = null,
        string $channelColumn = 'channel_id',
    ): Builder {
        // Apply base access scope
        $this->scopeToAccessibleChannels($query, $user, $channelColumn);

        // Apply optional filter (hub admin filtering by specific channel)
        if ($filterChannelId !== null) {
            $query->where($channelColumn, $filterChannelId);
        }

        return $query;
    }

    /**
     * Get all active channels the user can see (for filter dropdowns).
     *
     * Hub admins get all channels. Channel users get their assigned channels.
     *
     * @return Collection<int, DistributionChannel>
     */
    public function availableChannelsForFilter(?User $user): Collection
    {
        $instanceId = CurrentInstance::get()?->id;

        $query = DistributionChannel::withoutGlobalScopes()
            ->where('is_active', true)
            ->when($instanceId, fn ($q) => $q->where('instance_id', $instanceId))
            ->orderBy('name');

        if (!$this->isHubAdmin($user)) {
            $accessible = $this->accessibleChannelIds($user);
            if (!$accessible || $accessible->isEmpty()) {
                return collect();
            }
            $query->whereIn('id', $accessible);
        }

        return $query->get();
    }

    private function normalizeRole(?string $role): ?string
    {
        return match ($role) {
            'member' => 'admin',
            default => $role,
        };
    }
}
