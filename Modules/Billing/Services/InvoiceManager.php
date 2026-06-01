<?php

namespace Modules\Billing\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Payment;
use Modules\Billing\Models\Subscription;

final class InvoiceManager
{
    /**
     * Maximum number of retries when the generated invoice number collides
     * with an existing row. The UNIQUE constraint on `invoices.number`
     * rejects the insert, we regenerate and try again. After N attempts
     * we re-throw so that a real systemic issue (not a race) surfaces.
     */
    private const MAX_NUMBER_ATTEMPTS = 5;

    public function forInstance(int $instanceId): Collection
    {
        return Invoice::where('instance_id', $instanceId)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Generate an invoice for a subscription with atomic number allocation.
     *
     * R-202 — La numérotation n'est pas protégée par un lock pessimiste sur
     * un compteur (pas de séquence DB applicative). À la place :
     *   1. `nextNumber()` lit le MAX courant (rapide, non atomique seul) ;
     *   2. `Invoice::create()` s'exécute sous la contrainte UNIQUE globale
     *      sur `invoices.number` ;
     *   3. Si deux appels concurrents produisent le même numéro, la 2ᵉ
     *      insertion lève `QueryException` 1062 ; on rattrape, on régénère,
     *      on retente jusqu'à MAX_NUMBER_ATTEMPTS.
     *
     * Même pattern que `Modules\Eshop360\Services\InvoiceService` et
     * `Modules\Eshop360\Services\OrderService`. Voir ADR-006.
     */
    public function generate(Subscription $sub, string $billingPeriod = 'monthly'): Invoice
    {
        $plan = $sub->plan;
        $amount = $billingPeriod === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        $tax = 0;
        $total = $amount + $tax;

        return DB::transaction(function () use ($sub, $billingPeriod, $amount, $tax, $total) {
            for ($attempt = 1; $attempt <= self::MAX_NUMBER_ATTEMPTS; $attempt++) {
                try {
                    return Invoice::create([
                        'instance_id' => $sub->instance_id,
                        'subscription_id' => $sub->id,
                        'number' => $this->nextNumber(),
                        'amount' => $amount,
                        'tax' => $tax,
                        'total' => $total,
                        'currency' => function_exists('currency') ? currency($sub->instance_id) : config('billing.currency', 'EUR'),
                        'status' => 'pending',
                        'due_date' => now()->addDays(30),
                        'metadata' => ['billing_period' => $billingPeriod],
                    ]);
                } catch (QueryException $e) {
                    // MySQL/SQLite error 1062 = duplicate entry on UNIQUE(number).
                    // Une autre requête a pris le numéro entre notre SELECT MAX et
                    // notre INSERT → on régénère et on retente.
                    if ($attempt >= self::MAX_NUMBER_ATTEMPTS || (int) ($e->errorInfo[1] ?? 0) !== 1062) {
                        throw $e;
                    }
                    // Loop continues; nextNumber() will re-query MAX+1.
                }
            }

            throw new \RuntimeException('Failed to allocate a unique invoice number after '.self::MAX_NUMBER_ATTEMPTS.' attempts.');
        });
    }

    public function markPaid(Invoice $invoice, string $method, ?string $reference = null): Payment
    {
        $now = now();

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $invoice->total,
            'currency' => $invoice->currency,
            'method' => $method,
            'status' => 'completed',
            'reference' => $reference,
            'paid_at' => $now,
        ]);

        $invoice->update([
            'status' => 'paid',
            'paid_at' => $now,
        ]);

        return $payment;
    }

    public function markFailed(Invoice $invoice): Invoice
    {
        $invoice->update(['status' => 'failed']);

        return $invoice->refresh();
    }

    public function nextNumber(): string
    {
        $prefix = config('billing.invoice_prefix', 'B360-INV');
        $year = now()->year;

        $last = DB::connection('system')
            ->table('invoices')
            ->where('number', 'like', "{$prefix}-{$year}-%")
            ->orderByDesc('number')
            ->value('number');

        if ($last) {
            $seq = (int) substr($last, strrpos($last, '-') + 1);
            $next = $seq + 1;
        } else {
            $next = 1;
        }

        return sprintf('%s-%d-%05d', $prefix, $year, $next);
    }

    public function overdue(): Collection
    {
        return Invoice::where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->orderBy('due_date')
            ->get();
    }
}
