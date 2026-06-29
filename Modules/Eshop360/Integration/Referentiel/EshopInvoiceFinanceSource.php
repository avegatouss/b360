<?php

declare(strict_types=1);

namespace Modules\Eshop360\Integration\Referentiel;

use Modules\Core\Database\Scopes\InstanceScope;
use Modules\Eshop360\Database\Scopes\ChannelScope;
use Modules\Eshop360\Domain\Finance\Models\Invoice;
use Modules\Referentiel360\Contracts\Finance\FinanceAttributesDto;
use Modules\Referentiel360\Contracts\Finance\FinanceSource;

/**
 * Lot 3.b (ADR-031) — FinanceSource backfill des invoices Eshop360.
 *
 * Taggée `referentiel.finance_source` (cf. ServiceProvider, uniquement si
 * Referentiel360 est activé). Itère les invoices de l'instance (TOUS canaux
 * confondus — 1 document miroir par row) et yield des DTO neutres pour la
 * réconciliation idempotente (`referentiel:backfill-finance`).
 *
 * Bypass InstanceScope ET ChannelScope : le backfill couvre toute l'instance
 * indépendamment du canal courant et de l'utilisateur authentifié (sinon le
 * backfill CLI ne voit rien).
 */
final class EshopInvoiceFinanceSource implements FinanceSource
{
    public function __construct(
        private readonly EshopFinanceMapper $mapper,
    ) {}

    public function linkType(): string
    {
        return 'eshop.invoice';
    }

    /**
     * @return iterable<int, FinanceAttributesDto>
     */
    public function each(int $instanceId): iterable
    {
        $invoices = Invoice::query()
            ->withoutGlobalScope(InstanceScope::class)
            ->withoutGlobalScope(ChannelScope::class)
            ->where('instance_id', $instanceId)
            ->orderBy('id')
            ->cursor();

        foreach ($invoices as $invoice) {
            yield $this->mapper->fromInvoice($invoice);
        }
    }
}
