<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Integration\Referentiel;

use Modules\Core\Database\Scopes\InstanceScope;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;
use Modules\Referentiel360\Contracts\Finance\FinanceAttributesDto;
use Modules\Referentiel360\Contracts\Finance\FinanceSource;

/**
 * Lot 3.a (ADR-031, ZONE L1) — FinanceSource backfill des factures Menuiserie360.
 *
 * Taggée `referentiel.finance_source` (cf. ServiceProvider, uniquement si
 * Referentiel360 est activé). Itère les factures `mnu_invoices` de l'instance et
 * yield des DTO neutres pour la réconciliation idempotente
 * (`referentiel:backfill-finance`).
 */
final class MenuiserieInvoiceFinanceSource implements FinanceSource
{
    public function __construct(
        private readonly MenuiserieFinanceMapper $mapper,
    ) {}

    public function linkType(): string
    {
        return 'mnu.invoice';
    }

    /**
     * @return iterable<int, FinanceAttributesDto>
     */
    public function each(int $instanceId): iterable
    {
        $invoices = MenuiserieInvoice::query()
            ->withoutGlobalScope(InstanceScope::class)
            ->where('instance_id', $instanceId)
            ->orderBy('id')
            ->cursor();

        foreach ($invoices as $invoice) {
            yield $this->mapper->fromInvoice($invoice);
        }
    }
}
