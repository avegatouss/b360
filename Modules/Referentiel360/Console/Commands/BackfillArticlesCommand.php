<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Console\Commands;

use App\Instances\Instance;
use Illuminate\Console\Command;
use Modules\Referentiel360\Contracts\Article\ArticleSource;
use Modules\Referentiel360\Contracts\Article\ArticleWriter;
use Modules\Referentiel360\Domain\Article\Services\ArticleMatcher;
use Modules\Referentiel360\Domain\Article\Services\BackfillArticlesService;

/**
 * ADR-030 / Lot 2 — Réconciliation idempotente des articles (catalogue).
 *
 * Itère les `ArticleSource` taggées `referentiel.article_source` (implémentées
 * par les modules L3 en Lots 2.a/2.b). En Lot 2 (socle), aucune source n'est
 * encore enregistrée → la commande est un no-op sûr.
 *
 * `--dry-run` : simule, compte les créations/matchs, n'écrit rien.
 */
final class BackfillArticlesCommand extends Command
{
    protected $signature = 'referentiel:backfill-articles {--instance= : UUID ou ID numérique d\'une instance précise} {--dry-run : Simuler sans écrire}';

    protected $description = 'Réconcilie (par lien) les articles des modules métier dans le golden record ref_articles.';

    public function handle(ArticleMatcher $matcher, ArticleWriter $writer): int
    {
        $dryRun = (bool) $this->option('dry-run');

        /** @var array<int, ArticleSource> $sources */
        $sources = iterator_to_array(app()->tagged('referentiel.article_source'), false);

        if ($sources === []) {
            $this->info('Aucune ArticleSource enregistrée — backfill no-op (les Lots 2.a/2.b les fourniront).');

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

        $service = new BackfillArticlesService($sources, $matcher, $writer);
        $report = $service->run($instanceIds, $dryRun);

        $this->info('Backfill terminé.');
        $this->line("  Articles créés   : {$report->created}");
        $this->line("  Articles matchés : {$report->matched}");
        $this->line("  Liens écrits     : {$report->linked}");

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
