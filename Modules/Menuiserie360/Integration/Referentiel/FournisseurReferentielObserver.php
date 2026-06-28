<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Integration\Referentiel;

use Illuminate\Support\Facades\DB;
use Modules\Menuiserie360\Domain\Purchasing\Models\Fournisseur;
use Modules\Referentiel360\Contracts\Party\PartyWriter;

/**
 * Lot 1.a (ADR-030) — Observer best-effort Fournisseur → Referentiel360.
 *
 * Sur created/updated, pousse le fournisseur vers le golden record APRÈS commit
 * de la transaction Menuiserie (DB::afterCommit), de façon best-effort : toute
 * exception est rapportée (report) mais JAMAIS propagée — la création/màj du
 * fournisseur Menuiserie ne doit jamais échouer à cause du référentiel
 * (ADR-023 : Menuiserie reste autonome).
 *
 * Attaché uniquement si Referentiel360 est activé (cf. ServiceProvider).
 */
final class FournisseurReferentielObserver
{
    public function __construct(
        private readonly PartyWriter $writer,
        private readonly MenuiseriePartyMapper $mapper,
    ) {}

    public function created(Fournisseur $fournisseur): void
    {
        $this->push($fournisseur);
    }

    public function updated(Fournisseur $fournisseur): void
    {
        $this->push($fournisseur);
    }

    private function push(Fournisseur $fournisseur): void
    {
        $instanceId = (int) $fournisseur->getAttribute('instance_id');
        $attrs = $this->mapper->fromFournisseur($fournisseur);

        DB::afterCommit(function () use ($instanceId, $attrs): void {
            try {
                $this->writer->upsertFromModule($instanceId, 'mnu.supplier', $attrs);
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }
}
