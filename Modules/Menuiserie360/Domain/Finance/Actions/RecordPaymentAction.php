<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Finance\Actions;

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Modules\Menuiserie360\Domain\Finance\Enums\StatutFacture;
use Modules\Menuiserie360\Domain\Finance\Enums\StatutPaiement;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiseriePayment;

/**
 * P3-2 — Enregistre un paiement sur une facture menuiserie
 * (manual entry comptable OU webhook Mobile Money / CinetPay).
 *
 * Garanties :
 *   - Idempotence ADR-003 : `idempotency_key` UNIQUE par instance →
 *     re-jeu webhook silencieux (retourne le payment existant).
 *   - Atomicité : DB::transaction + lockForUpdate sur l'invoice
 *     pour éviter les races à la mise à jour cumulative `paid_amount`.
 *   - Status auto : invoice.status passe à PAID si paid_amount >= TTC,
 *     sinon PARTIAL_PAID dès le premier paiement encaissé.
 */
final class RecordPaymentAction
{
    /**
     * @param  array{
     *     amount: float,
     *     method: string,
     *     gateway?: string|null,
     *     transaction_ref?: string|null,
     *     idempotency_key?: string|null,
     *     gateway_payload?: array<string, mixed>|null,
     *     paid_at?: \DateTimeInterface|null,
     * }  $payload
     */
    public function execute(MenuiserieInvoice $invoice, array $payload): MenuiseriePayment
    {
        $idempotencyKey = $payload['idempotency_key'] ?? null;

        if ($idempotencyKey !== null) {
            $existing = MenuiseriePayment::query()
                ->where('instance_id', $invoice->getAttribute('instance_id'))
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing instanceof MenuiseriePayment) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($invoice, $payload, $idempotencyKey) {
            $locked = MenuiserieInvoice::query()
                ->where('id', $invoice->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            try {
                $payment = MenuiseriePayment::create([
                    'instance_id' => $locked->getAttribute('instance_id'),
                    'payable_type' => 'mnu.invoice',
                    'payable_id' => $locked->getKey(),
                    'amount' => $payload['amount'],
                    'method' => $payload['method'],
                    'gateway' => $payload['gateway'] ?? null,
                    'transaction_ref' => $payload['transaction_ref'] ?? null,
                    'idempotency_key' => $idempotencyKey,
                    'status' => StatutPaiement::SUCCEEDED->value,
                    'paid_at' => $payload['paid_at'] ?? now(),
                    'gateway_payload' => $payload['gateway_payload'] ?? null,
                ]);
            } catch (UniqueConstraintViolationException) {
                $existing = MenuiseriePayment::query()
                    ->where('instance_id', $locked->getAttribute('instance_id'))
                    ->where('idempotency_key', $idempotencyKey)
                    ->firstOrFail();

                return $existing;
            }

            $newPaid = (float) $locked->getAttribute('paid_amount') + (float) $payload['amount'];
            $ttc = (float) $locked->getAttribute('amount_ttc');

            $locked->setAttribute('paid_amount', $newPaid);
            $locked->setAttribute('status', $newPaid >= $ttc
                ? StatutFacture::PAID_FULL->value
                : StatutFacture::PAID_PARTIAL->value);
            $locked->save();

            return $payment;
        });
    }
}
