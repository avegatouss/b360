<?php

declare(strict_types=1);

namespace Modules\Eshop360\Integration\Referentiel;

use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Domain\Catalog\Models\Product;
use Modules\Referentiel360\Contracts\Article\ArticleWriter;

/**
 * Lot 2.b (ADR-030) — Observer best-effort Product → Referentiel360.
 *
 * Sur created/updated, pousse le product vers le golden record APRÈS commit de
 * la transaction Eshop (DB::afterCommit), de façon best-effort : toute exception
 * est rapportée (report) mais JAMAIS propagée — la création/màj du product Eshop
 * ne doit jamais échouer à cause du référentiel (Eshop reste autonome).
 *
 * Attaché uniquement si Referentiel360 est activé (cf. ServiceProvider).
 */
final class ProductReferentielObserver
{
    public function __construct(
        private readonly ArticleWriter $writer,
        private readonly EshopArticleMapper $mapper,
    ) {}

    public function created(Product $product): void
    {
        $this->push($product);
    }

    public function updated(Product $product): void
    {
        $this->push($product);
    }

    private function push(Product $product): void
    {
        $instanceId = (int) $product->getAttribute('instance_id');
        $attrs = $this->mapper->fromProduct($product);

        DB::afterCommit(function () use ($instanceId, $attrs): void {
            try {
                $this->writer->upsertFromModule($instanceId, 'eshop.product', $attrs);
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }
}
