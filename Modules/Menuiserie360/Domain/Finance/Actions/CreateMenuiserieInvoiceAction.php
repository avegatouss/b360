<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Finance\Actions;

use Modules\Menuiserie360\Domain\Finance\Enums\StatutFacture;
use Modules\Menuiserie360\Domain\Finance\Enums\TypeFacture;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;
use Modules\Menuiserie360\Domain\Finance\Services\InvoiceNumberGenerator;
use Modules\Menuiserie360\Domain\Sales\Models\BonCommande;

/**
 * P2-7 part 3 — Crée une facture menuiserie native (acompte ou solde).
 *
 * Utilise InvoiceNumberGenerator pour la numérotation atomique pattern ADR-006.
 * Calcule le montant à facturer selon le type :
 *   - acompte : montant_ttc * acompte_pct / 100
 *   - solde   : montant_ttc - paid_amount cumulé acomptes
 *
 * Idempotence par bc_id + type : si une facture du même type existe déjà
 * pour ce BC, retourne l'existante (évite double facturation).
 */
final class CreateMenuiserieInvoiceAction
{
    public function __construct(
        private readonly InvoiceNumberGenerator $numberGenerator,
    ) {}

    public function executeAcompte(BonCommande $bc): MenuiserieInvoice
    {
        return $this->execute($bc, TypeFacture::ACOMPTE);
    }

    public function executeSolde(BonCommande $bc): MenuiserieInvoice
    {
        return $this->execute($bc, TypeFacture::SOLDE);
    }

    private function execute(BonCommande $bc, TypeFacture $type): MenuiserieInvoice
    {
        // Idempotence : facture du même type pour ce BC.
        $existing = MenuiserieInvoice::query()
            ->where('instance_id', $bc->getAttribute('instance_id'))
            ->where('bc_id', $bc->getKey())
            ->where('type', $type->value)
            ->first();

        if ($existing instanceof MenuiserieInvoice) {
            return $existing;
        }

        $amounts = $this->computeAmounts($bc, $type);

        return $this->numberGenerator->generateAndCreate(
            (int) $bc->getAttribute('instance_id'),
            fn (string $invoiceNumber) => MenuiserieInvoice::create([
                'instance_id' => $bc->getAttribute('instance_id'),
                'invoice_number' => $invoiceNumber,
                'client_id' => $bc->getAttribute('client_id'),
                'bc_id' => $bc->getKey(),
                'chantier_id' => $bc->getAttribute('chantier_id'),
                'type' => $type->value,
                'amount_ht' => $amounts['ht'],
                'tax_rate' => $bc->getAttribute('taux_tva'),
                'amount_tva' => $amounts['tva'],
                'amount_ttc' => $amounts['ttc'],
                'paid_amount' => 0,
                'status' => StatutFacture::ISSUED->value,
                'issued_at' => now(),
            ]),
        );
    }

    /**
     * @return array{ht: float, tva: float, ttc: float}
     */
    private function computeAmounts(BonCommande $bc, TypeFacture $type): array
    {
        $bcTtc = (float) $bc->getAttribute('montant_ttc');
        $tauxTva = (float) $bc->getAttribute('taux_tva');

        $ttc = match ($type) {
            TypeFacture::ACOMPTE => round($bcTtc * (float) $bc->getAttribute('acompte_pct') / 100.0, 2),
            TypeFacture::SOLDE => round($bcTtc - $this->totalAcomptes($bc), 2),
            TypeFacture::AVOIR => 0.0,
        };

        // Décomposition TTC → HT + TVA en respectant le taux exact (évite drift)
        $ht = $tauxTva > 0 ? round($ttc / (1.0 + $tauxTva), 2) : $ttc;
        $tva = round($ttc - $ht, 2);

        return ['ht' => $ht, 'tva' => $tva, 'ttc' => $ttc];
    }

    private function totalAcomptes(BonCommande $bc): float
    {
        return (float) MenuiserieInvoice::query()
            ->where('instance_id', $bc->getAttribute('instance_id'))
            ->where('bc_id', $bc->getKey())
            ->where('type', TypeFacture::ACOMPTE->value)
            ->sum('amount_ttc');
    }
}
