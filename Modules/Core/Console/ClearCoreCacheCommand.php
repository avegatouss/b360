<?php

namespace Modules\Core\Console;

use Illuminate\Console\Command;
use Modules\Core\Modules\ModuleManager;

final class ClearCoreCacheCommand extends Command
{
    protected $signature = 'core:cache:clear';
    protected $description = 'Clear Core caches (enabled modules, etc.).';

    public function handle(ModuleManager $modules): int
    {
        $modules->clearCache();
        $this->info('Core cache cleared.');
        return self::SUCCESS;
    }
}
