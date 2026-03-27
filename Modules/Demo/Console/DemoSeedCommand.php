<?php

namespace Modules\Demo\Console;

use Illuminate\Console\Command;
use Modules\Demo\Services\DemoManager;

class DemoSeedCommand extends Command
{
    protected $signature = 'demo:seed
        {--instance= : Instance ID to seed demo data for}
        {--module= : Seed only a specific module (e.g. Eshop360)}
        {--provider= : Seed only a specific provider ID}';

    protected $description = 'Install demo data via registered demo providers';

    public function handle(DemoManager $manager): int
    {
        $instanceId = (int) ($this->option('instance') ?: 1);

        if ($providerId = $this->option('provider')) {
            $this->info("Seeding provider: {$providerId}...");
            $result = $manager->seed($providerId, $instanceId);
            $this->line($result['message']);
            return $result['success'] ? 0 : 1;
        }

        if ($module = $this->option('module')) {
            $this->info("Seeding module: {$module}...");
            $results = $manager->seedModule($module, $instanceId);
        } else {
            $this->info('Seeding all demo data...');
            $results = $manager->seedAll($instanceId);
        }

        foreach ($results as $id => $result) {
            $icon = $result['success'] ? '<info>OK</info>' : '<error>FAIL</error>';
            $this->line("  [{$icon}] {$id}: {$result['message']}");
        }

        return 0;
    }
}
