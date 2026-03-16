<?php

use App\Services\SelectInventoryService;
use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('ui:inventory-selects {--write : Ecrit les artefacts d inventaire dans docs/ui}', function (SelectInventoryService $inventoryService) {
    /** @var ClosureCommand $this */
    $inventory = $inventoryService->buildInventory();

    $this->info('Selects detectes : ' . $inventory['totals']['selects']);
    $this->line('Selects en dur : ' . $inventory['totals']['hardcoded_selects']);
    $this->line('Selects anonymes : ' . $inventory['totals']['anonymous_selects']);

    if ($this->option('write')) {
        $paths = $inventoryService->writeArtifacts($inventory);
        $this->newLine();
        $this->info('Artefacts ecrits :');
        $this->line('- json: ' . $paths['json']);
        $this->line('- markdown: ' . $paths['markdown']);
        $this->line('- stub: ' . $paths['stub']);
    }
})->purpose('Inventorie tous les selects Blade et les selects avec options en dur');
