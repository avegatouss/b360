<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Integration\Referentiel;

use Illuminate\Support\Facades\DB;
use Modules\Menuiserie360\Domain\Catalog\Models\CatalogItem;
use Modules\Referentiel360\Contracts\Article\ArticleWriter;

/**
 * Lot 2.a (ADR-030) — Observer best-effort CatalogItem → Referentiel360.
 *
 * Sur created/updated, pousse l'item catalogue vers le golden record APRÈS
 * commit de la transaction Menuiserie (DB::afterCommit), de façon best-effort :
 * toute exception est rapportée (report) mais JAMAIS propagée — la création/màj
 * de l'item Menuiserie ne doit jamais échouer à cause du référentiel (ADR-023 :
 * Menuiserie reste autonome).
 *
 * Attaché uniquement si Referentiel360 est activé (cf. ServiceProvider).
 */
final class CatalogItemReferentielObserver
{
    public function __construct(
        private readonly ArticleWriter $writer,
        private readonly MenuiserieArticleMapper $mapper,
    ) {}

    public function created(CatalogItem $item): void
    {
        $this->push($item);
    }

    public function updated(CatalogItem $item): void
    {
        $this->push($item);
    }

    private function push(CatalogItem $item): void
    {
        $instanceId = (int) $item->getAttribute('instance_id');
        $attrs = $this->mapper->fromCatalogItem($item);

        DB::afterCommit(function () use ($instanceId, $attrs): void {
            try {
                $this->writer->upsertFromModule($instanceId, 'mnu.catalog_item', $attrs);
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }
}
