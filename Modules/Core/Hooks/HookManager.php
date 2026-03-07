<?php

namespace Modules\Core\Hooks;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Hooks\Contracts\RegistersHooks;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Modules\ModuleManager;

final class HookManager
{
    private bool $booted = false;

    public function __construct(
        private readonly HookRegistry $registry,
        private readonly ModuleManager $modules
    ) {}

    /**
     * @param array<int, class-string> $providers
     */
    public function boot(array $providers): void
    {
        if ($this->booted) {
            return;
        }
        $this->booted = true;

        foreach ($providers as $class) {
            if (!is_string($class) || $class === '' || !class_exists($class)) {
                continue;
            }

            // Anti-piège: si quelqu'un met un Laravel ServiceProvider ici, on skip
            if ($class === ServiceProvider::class || is_subclass_of($class, ServiceProvider::class)) {
                continue;
            }

            $obj = app($class);

            // 1) Chemin "contract-first" (recommandé)
            if ($obj instanceof RegistersHooks) {
                $module = trim((string) $obj->moduleName());
                if ($module !== '' && !$this->modules->isEnabled($module)) {
                    continue;
                }

                $obj->registerHooks($this->registry);
                continue;
            }

            // 2) Fallback legacy: méthodes présentes
            if (method_exists($obj, 'moduleName')) {
                $module = trim((string) $obj->moduleName());
                if ($module !== '' && !$this->modules->isEnabled($module)) {
                    continue;
                }
            }

            if (method_exists($obj, 'registerHooks')) {
                $obj->registerHooks($this->registry);
            }
        }
    }

    public function registry(): HookRegistry
    {
        return $this->registry;
    }
}
