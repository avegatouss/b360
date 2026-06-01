<?php

namespace Modules\Demo\Console;

use Illuminate\Console\Command;
use Modules\Demo\Services\DemoManager;

class DemoResetCommand extends Command
{
    protected $signature = 'demo:reset
        {--instance= : Instance ID to reset}
        {--provider= : Reset only a specific provider ID}
        {--force : Skip confirmation}';

    protected $description = 'Reset demo data to strict minimum base';

    public function handle(DemoManager $manager): int
    {
        $instanceId = (int) ($this->option('instance') ?: 1);

        if (!$this->option('force') && !$this->confirm('Ceci va supprimer les donnees de demo. Continuer ?')) {
            return 0;
        }

        if ($providerId = $this->option('provider')) {
            $this->info("Resetting provider: {$providerId}...");
            $result = $manager->reset($providerId, $instanceId);
            $this->line($result['message']);
            return $result['success'] ? 0 : 1;
        }

        $this->info('Resetting all demo data...');
        $results = $manager->resetAll($instanceId);

        foreach ($results as $id => $result) {
            $icon = $result['success'] ? '<info>OK</info>' : '<error>FAIL</error>';
            $this->line("  [{$icon}] {$id}: {$result['message']}");
        }

        return 0;
    }
}
