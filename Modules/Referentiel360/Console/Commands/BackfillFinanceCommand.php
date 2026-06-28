<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Console\Commands;

use App\Instances\Instance;
use Illuminate\Console\Command;
use Modules\Referentiel360\Contracts\Finance\FinanceSource;
use Modules\Referentiel360\Contracts\Finance\FinanceWriter;
use Modules\Referentiel360\Domain\Finance\Services\BackfillFinanceService;
use Modules\Referentiel360\Domain\Finance\Services\FinanceMatcher;

/**
 * ADR-031 / Lot 3 — Resync idempotent du registre financier miroir.
 *
 * Itère les `FinanceSource` taggées `referentiel.finance_source` (implémentées
 * par les modules L3 en Lots 3.a/3.b). En Lot 3 (socle), aucune source n'est
 * encore enregistrée → la commande est un no-op sûr. Corrige tout drift résiduel
 * des push best-effort ratés (full refresh des montants/statut).
 *
 * `--dry-run` : simule, compte les créations/matchs, n'écrit rien.
 */
final class BackfillFinanceCommand extends Command
{
    protected $signature = 'referentiel:backfill-finance {--instance= : UUID ou ID numérique d\'une instance précise} {--dry-run : Simuler sans écrire}';

    protected $description = 'Resynchronise (par lien, full refresh) les factures des modules métier dans le registre miroir ref_documents_finance.';

    public function handle(FinanceMatcher $matcher, FinanceWriter $writer): int
    {
        $dryRun = (bool) $this->option('dry-run');

        /** @var array<int, FinanceSource> $sources */
        $sources = array_values(app()->tagged('referentiel.finance_source'));

        if ($sources === []) {
            $this->info('Aucune FinanceSource enregistrée — backfill no-op (les Lots 3.a/3.b les fourniront).');

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

        $service = new BackfillFinanceService($sources, $matcher, $writer);
        $report = $service->run($instanceIds, $dryRun);

        $this->info('Backfill terminé.');
        $this->line("  Documents créés   : {$report->created}");
        $this->line("  Documents matchés : {$report->matched}");
        $this->line("  Liens écrits      : {$report->linked}");

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
