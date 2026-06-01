<?php

namespace Modules\Eshop360\Support;

use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Domain\Channel\Models\DistributionChannel;

/**
 * Manages the "active channel" context for the current session.
 *
 * When a user navigates into a channel via the hierarchical menu,
 * all subsequent pages are scoped to that channel's data.
 * Saphir Plus (global channel) = hub mode, sees everything.
 */
final class CurrentChannel
{
    private static ?DistributionChannel $resolved = null;

    private static function sessionKey(): string
    {
        $instanceId = CurrentInstance::get()?->id ?? 0;

        return "eshop_current_channel_{$instanceId}";
    }

    /**
     * Set the active channel for this session.
     */
    public static function set(DistributionChannel $channel): void
    {
        self::$resolved = $channel;
        session([self::sessionKey() => $channel->id]);
    }

    /**
     * Clear the active channel (return to hub/unscoped view).
     */
    public static function clear(): void
    {
        self::$resolved = null;
        session()->forget(self::sessionKey());
    }

    /**
     * Get the active channel from session (lazy-loaded).
     */
    public static function get(): ?DistributionChannel
    {
        if (self::$resolved !== null) {
            return self::$resolved;
        }

        $channelId = session(self::sessionKey());
        if (! $channelId) {
            return null;
        }

        $instance = CurrentInstance::get();
        if (! $instance) {
            return null;
        }

        $channel = DistributionChannel::withoutGlobalScopes()
            ->where('instance_id', $instance->id)
            ->where('id', $channelId)
            ->where('is_active', true)
            ->first();

        if (! $channel) {
            session()->forget(self::sessionKey());

            return null;
        }

        // Verify the authenticated user can actually access this channel
        if (auth()->check()) {
            $access = app(\Modules\Eshop360\Services\ChannelAccessService::class);
            if (! $access->canAccessChannel(auth()->user(), $channel)) {
                session()->forget(self::sessionKey());

                return null;
            }
        }

        self::$resolved = $channel;

        return self::$resolved;
    }

    /**
     * Get the active channel ID, or null if none.
     */
    public static function id(): ?int
    {
        return self::get()?->id;
    }

    /**
     * Is the current channel the global hub (Saphir Plus)?
     */
    public static function isHub(): bool
    {
        $channel = self::get();
        if (! $channel) {
            return true; // No channel selected = hub mode
        }

        return (bool) $channel->is_hub;
    }

    /**
     * Is a non-hub channel currently active? (data should be scoped)
     */
    public static function isScoped(): bool
    {
        return self::get() !== null && ! self::isHub();
    }

    /**
     * Reset in-memory cache (for testing).
     */
    public static function flush(): void
    {
        self::$resolved = null;
    }
}
