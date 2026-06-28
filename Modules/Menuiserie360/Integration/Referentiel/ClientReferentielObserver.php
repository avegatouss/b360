<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Integration\Referentiel;

use Illuminate\Support\Facades\DB;
use Modules\Menuiserie360\Domain\Client\Models\ClientMenuiserie;
use Modules\Referentiel360\Contracts\Party\PartyWriter;

/**
 * Lot 1.a (ADR-030) — Observer best-effort ClientMenuiserie → Referentiel360.
 *
 * Sur created/updated, pousse le client vers le golden record APRÈS commit de
 * la transaction Menuiserie (DB::afterCommit), de façon best-effort : toute
 * exception est rapportée (report) mais JAMAIS propagée — la création/màj du
 * client Menuiserie ne doit jamais échouer à cause du référentiel (ADR-023 :
 * Menuiserie reste autonome).
 *
 * Attaché uniquement si Referentiel360 est activé (cf. ServiceProvider).
 */
final class ClientReferentielObserver
{
    public function __construct(
        private readonly PartyWriter $writer,
        private readonly MenuiseriePartyMapper $mapper,
    ) {}

    public function created(ClientMenuiserie $client): void
    {
        $this->push($client);
    }

    public function updated(ClientMenuiserie $client): void
    {
        $this->push($client);
    }

    private function push(ClientMenuiserie $client): void
    {
        $instanceId = (int) $client->getAttribute('instance_id');
        $attrs = $this->mapper->fromClient($client);

        DB::afterCommit(function () use ($instanceId, $attrs): void {
            try {
                $this->writer->upsertFromModule($instanceId, 'mnu.client', $attrs);
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }
}
