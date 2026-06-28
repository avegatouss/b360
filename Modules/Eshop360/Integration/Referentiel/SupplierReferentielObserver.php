<?php

declare(strict_types=1);

namespace Modules\Eshop360\Integration\Referentiel;

use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Domain\Purchasing\Models\Supplier;
use Modules\Referentiel360\Contracts\Party\PartyWriter;

/**
 * Lot 1.b (ADR-030) — Observer best-effort Supplier → Referentiel360.
 *
 * Sur created/updated, pousse le supplier vers le golden record APRÈS commit de
 * la transaction Eshop (DB::afterCommit), de façon best-effort : toute exception
 * est rapportée (report) mais JAMAIS propagée — la création/màj du supplier
 * Eshop ne doit jamais échouer à cause du référentiel (Eshop reste autonome).
 *
 * Attaché uniquement si Referentiel360 est activé (cf. ServiceProvider).
 */
final class SupplierReferentielObserver
{
    public function __construct(
        private readonly PartyWriter $writer,
        private readonly EshopPartyMapper $mapper,
    ) {}

    public function created(Supplier $supplier): void
    {
        $this->push($supplier);
    }

    public function updated(Supplier $supplier): void
    {
        $this->push($supplier);
    }

    private function push(Supplier $supplier): void
    {
        $instanceId = (int) $supplier->getAttribute('instance_id');
        $attrs = $this->mapper->fromSupplier($supplier);

        DB::afterCommit(function () use ($instanceId, $attrs): void {
            try {
                $this->writer->upsertFromModule($instanceId, 'eshop.supplier', $attrs);
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }
}
