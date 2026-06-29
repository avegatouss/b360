<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Console\Commands;

use App\Instances\Instance;
use Illuminate\Console\Command;
use Modules\Referentiel360\Contracts\Party\PartySource;
use Modules\Referentiel360\Contracts\Party\PartyWriter;
use Modules\Referentiel360\Domain\Party\Services\BackfillTiersService;
use Modules\Referentiel360\Domain\Party\Services\PartyMatcher;

/**
 * ADR-030 / Lot 1 — Réconciliation idempotente des tiers (clients + fournisseurs).
 *
 * Itère les `PartySource` taggées `referentiel.party_source` (implémentées par
 * les modules L3 en Lots 1.a/1.b). En Lot 1, aucune source n'est encore
 * enregistrée → la commande est un no-op sûr.
 *
 * `--dry-run` : simule, compte les créations/matchs/collisions, n'écrit rien.
 */
final class BackfillTiersCommand extends Command
{
    protected $signature = 'referentiel:backfill-tiers {--instance= : UUID ou ID numérique d\'une instance précise} {--dry-run : Simuler sans écrire}';

    protected $description = 'Réconcilie (déduplique) les tiers des modules métier dans le golden record ref_parties.';

    public function handle(PartyMatcher $matcher, PartyWriter $writer): int
    {
        $dryRun = (bool) $this->option('dry-run');

        /** @var array<int, PartySource> $sources */
        $sources = iterator_to_array(app()->tagged('referentiel.party_source'), false);

        if ($sources === []) {
            $this->info('Aucune PartySource enregistrée — backfill no-op (les Lots 1.a/1.b les fourniront).');

            return self::SUCCESS;
        }

        $instanceIds = $this->resolveInstanceIds();
        if ($instanceIds === []) {
            $this->warn('Aucune instance ciblée.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn('Mode --dry-run : aucune écriture ne sera effectuée.');
        }

        $service = new BackfillTiersService($sources, $matcher, $writer);
        $report = $service->run($instanceIds, $dryRun);

        $this->info('Backfill terminé.');
        $this->line("  Parties créées   : {$report->created}");
        $this->line("  Parties matchées : {$report->matched}");
        $this->line("  Liens écrits     : {$report->linked}");
        $this->line('  Collisions (review, NON fusionnées) : '.$report->reviewCount());

        foreach ($report->reviews as $review) {
            $this->warn(sprintf(
                '  [review] instance=%d %s#%d rule=%s — %s',
                $review['instance_id'],
                $review['link_type'],
                $review['local_id'],
                $review['rule'] ?? '?',
                $review['reason'] ?? '',
            ));
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, int>
     */
    private function resolveInstanceIds(): array
    {
        $option = $this->option('instance');

        if ($option !== null && $option !== '') {
            $instance = is_numeric($option)
                ? Instance::find((int) $option)
                : Instance::where('uuid', $option)->first();

            if ($instance === null) {
                $this->error("Instance introuvable : {$option}");

                return [];
            }

            return [(int) $instance->id];
        }

        return Instance::query()
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }
}
