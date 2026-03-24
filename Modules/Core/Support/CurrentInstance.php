<?php

namespace Modules\Core\Support;

use App\Instances\Instance;

final class CurrentInstance
{
    public static function get(): ?Instance
    {
        /** @var Instance|null $instance */
        $instance = app()->bound('currentInstance') ? app('currentInstance') : null;
        return $instance instanceof Instance ? $instance : null;
    }

    public static function set(?Instance $instance): void
    {
        app()->instance('currentInstance', $instance);
    }

    /**
     * Get the current instance ID or throw if none is resolved.
     * Use this instead of CurrentInstance::get()?->id ?? 0 to fail loud.
     */
    public static function idOrFail(): int
    {
        $instance = static::get();

        if (!$instance?->id) {
            abort(503, 'Instance non résolue — impossible de continuer.');
        }

        return (int) $instance->id;
    }

    public static function clear(): void
    {
        app()->forgetInstance('currentInstance');
    }
}
