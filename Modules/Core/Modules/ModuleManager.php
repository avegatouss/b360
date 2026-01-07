<?php

namespace Modules\Core\Modules;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class ModuleManager
{
    public function isEnabled(string $name): bool
    {
        $enabled = $this->enabledModules();
        return in_array(strtoupper($name), $enabled, true);
    }

    /**
     * @return array<int, string> list of enabled module names (uppercase)
     */
    public function enabledModules(): array
    {
        $ttl = (int) config('core.cache.enabled_modules_ttl_seconds', 60);

        return Cache::remember('core.enabled_modules', $ttl, function () {
            return DB::connection('system')
                ->table('modules')
                ->where('is_enabled', true)
                ->pluck('name')
                ->map(fn($x) => strtoupper((string) $x))
                ->values()
                ->all();
        });
    }

    public function clearCache(): void
    {
        Cache::forget('core.enabled_modules');
    }
}
