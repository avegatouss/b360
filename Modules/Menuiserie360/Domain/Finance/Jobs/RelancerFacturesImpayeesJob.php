<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Finance\Jobs;

use App\Instances\Instance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Menuiserie360\Domain\Finance\Services\RelanceService;

/**
 * P3-7 — Job programmé qui lance les relances factures impayées
 * pour TOUTES les instances actives. Suggéré planning : daily 08:00.
 *
 * Idempotent — repose sur le cooldown de RelanceService (7j par défaut)
 * pour éviter les doublons en cas de re-déclenchement.
 */
final class RelancerFacturesImpayeesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $delaiJours = 30,
        public readonly int $cooldownJours = 7,
    ) {}

    public function handle(RelanceService $service): void
    {
        Instance::query()
            ->where('is_active', true)
            ->each(function (Instance $instance) use ($service) {
                $service->relancerFacturesImpayees(
                    instanceId: (int) $instance->id,
                    delaiJours: $this->delaiJours,
                    cooldownJours: $this->cooldownJours,
                );
            });
    }
}
