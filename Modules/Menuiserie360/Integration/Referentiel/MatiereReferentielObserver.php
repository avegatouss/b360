<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Integration\Referentiel;

use Illuminate\Support\Facades\DB;
use Modules\Menuiserie360\Domain\Stock\Models\MatierePremiere;
use Modules\Referentiel360\Contracts\Article\ArticleWriter;

/**
 * Lot 2.a (ADR-030) — Observer best-effort MatierePremiere → Referentiel360.
 *
 * Sur created/updated, pousse la matière première vers le golden record APRÈS
 * commit de la transaction Menuiserie (DB::afterCommit), de façon best-effort :
 * toute exception est rapportée (report) mais JAMAIS propagée — la création/màj
 * de la matière Menuiserie ne doit jamais échouer à cause du référentiel
 * (ADR-023 : Menuiserie reste autonome).
 *
 * Attaché uniquement si Referentiel360 est activé (cf. ServiceProvider).
 */
final class MatiereReferentielObserver
{
    public function __construct(
        private readonly ArticleWriter $writer,
        private readonly MenuiserieArticleMapper $mapper,
    ) {}

    public function created(MatierePremiere $matiere): void
    {
        $this->push($matiere);
    }

    public function updated(MatierePremiere $matiere): void
    {
        $this->push($matiere);
    }

    private function push(MatierePremiere $matiere): void
    {
        $instanceId = (int) $matiere->getAttribute('instance_id');
        $attrs = $this->mapper->fromMatiere($matiere);

        DB::afterCommit(function () use ($instanceId, $attrs): void {
            try {
                $this->writer->upsertFromModule($instanceId, 'mnu.matiere', $attrs);
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }
}
