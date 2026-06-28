<?php

declare(strict_types=1);

namespace Modules\Eshop360\Integration\Referentiel;

use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Referentiel360\Contracts\Party\PartyWriter;

/**
 * Lot 1.b (ADR-030) — Observer best-effort Customer → Referentiel360.
 *
 * Sur created/updated, pousse le customer vers le golden record APRÈS commit de
 * la transaction Eshop (DB::afterCommit), de façon best-effort : toute exception
 * est rapportée (report) mais JAMAIS propagée — la création/màj du customer
 * Eshop ne doit jamais échouer à cause du référentiel (Eshop reste autonome).
 *
 * Attaché uniquement si Referentiel360 est activé (cf. ServiceProvider).
 */
final class CustomerReferentielObserver
{
    public function __construct(
        private readonly PartyWriter $writer,
        private readonly EshopPartyMapper $mapper,
    ) {}

    public function created(Customer $customer): void
    {
        $this->push($customer);
    }

    public function updated(Customer $customer): void
    {
        $this->push($customer);
    }

    private function push(Customer $customer): void
    {
        $instanceId = (int) $customer->getAttribute('instance_id');
        $attrs = $this->mapper->fromCustomer($customer);

        DB::afterCommit(function () use ($instanceId, $attrs): void {
            try {
                $this->writer->upsertFromModule($instanceId, 'eshop.customer', $attrs);
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }
}
