<?php

namespace Modules\Core\Modules;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;

class ModuleManager
{
    /** @var array<int, string>|null */
    private ?array $enabledModulesCache = null;

    public function isEnabled(string $name): bool
    {
        $enabled = $this->enabledModules();
        return in_array(strtoupper($name), $enabled, true);
    }

    /**
     * @return array<int, string> list of enabled module names (uppercase)
     *
     * Cascade: DB modules table first, then fallback to nwidart modules_statuses.json
     * for modules not yet registered in DB (e.g. fresh install before seeding).
     */
    public function enabledModules(): array
    {
        if ($this->enabledModulesCache !== null && Cache::has('core.enabled_modules')) {
            return $this->enabledModulesCache;
        }

        if (! Cache::has('core.enabled_modules')) {
            $this->enabledModulesCache = null;
        }

        $ttl = (int) config('core.cache.enabled_modules_ttl_seconds', 60);

        $enabled = Cache::remember('core.enabled_modules', $ttl, function () {
            // 1. Modules explicitly tracked in DB
            $dbModules = collect();
            try {
                $dbModules = DB::connection('system')
                    ->table('modules')
                    ->get()
                    ->keyBy(fn ($m) => strtoupper((string) $m->name));
            } catch (\Throwable) {
                // Table may not exist yet during install
            }

            // 2. Merge with nwidart statuses for modules not in DB
            $enabled = [];

            foreach (Module::all() as $mod) {
                $nameUpper = strtoupper($mod->getName());
                $dbRecord = $dbModules->get($nameUpper);

                if ($dbRecord !== null) {
                    // DB is authoritative when present
                    if ($dbRecord->is_enabled) {
                        $enabled[] = $nameUpper;
                    }
                } else {
                    // Fallback to nwidart modules_statuses.json
                    if ($mod->isEnabled()) {
                        $enabled[] = $nameUpper;
                    }
                }
            }

            return $enabled;
        });

        return $this->enabledModulesCache = $enabled;
    }

    public function clearCache(): void
    {
        $this->enabledModulesCache = null;
        Cache::forget('core.enabled_modules');
    }
}
