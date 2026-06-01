<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Finance\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Menuiserie360\Domain\Finance\Enums\StatutFacture;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;

/**
 * P3-7 — Service de relance automatique des factures impayées.
 *
 * Logique :
 *   - Cible : factures `ISSUED` ou `PAID_PARTIAL` dont
 *     `issued_at` < (now - $delaiJours).
 *   - Anti-flood : skip si `last_relance_at` >= (now - $cooldownJours).
 *   - Effet : incrémente `relance_count`, met à jour `last_relance_at`.
 *     L'envoi effectif (mail / SMS) sera branché dans une V2 avec
 *     Notification Laravel ; ici on log au minimum pour audit.
 *
 * Idempotent à exécutions répétées dans le même cooldown : aucun double envoi.
 */
final class RelanceService
{
    /**
     * Lance les relances pour l'instance donnée.
     *
     * @return array<int, int> IDs des factures relancées
     */
    public function relancerFacturesImpayees(
        int $instanceId,
        int $delaiJours = 30,
        int $cooldownJours = 7,
    ): array {
        $now = CarbonImmutable::now();
        $seuilEmission = $now->subDays($delaiJours);
        $seuilCooldown = $now->subDays($cooldownJours);

        $candidates = MenuiserieInvoice::query()
            ->where('instance_id', $instanceId)
            ->whereIn('status', [StatutFacture::ISSUED->value, StatutFacture::PAID_PARTIAL->value])
            ->where('issued_at', '<', $seuilEmission)
            ->where(fn ($q) => $q->whereNull('last_relance_at')
                ->orWhere('last_relance_at', '<', $seuilCooldown))
            ->get();

        $relancees = [];
        foreach ($candidates as $invoice) {
            DB::transaction(function () use ($invoice, $now) {
                $invoice->setAttribute(
                    'relance_count',
                    (int) $invoice->getAttribute('relance_count') + 1
                );
                $invoice->setAttribute('last_relance_at', $now);
                $invoice->save();
            });

            Log::info('menuiserie360.invoice.relance', [
                'instance_id' => $instanceId,
                'invoice_id' => $invoice->getKey(),
                'invoice_number' => $invoice->getAttribute('invoice_number'),
                'relance_count' => $invoice->getAttribute('relance_count'),
                'due_amount' => $invoice->dueAmount(),
            ]);

            $relancees[] = (int) $invoice->getKey();
        }

        return $relancees;
    }
}
