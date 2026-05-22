<?php

namespace Modules\Billing\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Payment;
use Modules\Billing\Models\Subscription;

final class InvoiceManager
{
    public function forInstance(int $instanceId): Collection
    {
        return Invoice::where('instance_id', $instanceId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function generate(Subscription $sub): Invoice
    {
        $plan = $sub->plan;
        $amount = $plan->price_monthly;
        $tax = 0;
        $total = $amount + $tax;

        return Invoice::create([
            'instance_id' => $sub->instance_id,
            'subscription_id' => $sub->id,
            'number' => $this->nextNumber(),
            'amount' => $amount,
            'tax' => $tax,
            'total' => $total,
            'currency' => config('billing.currency', 'EUR'),
            'status' => 'pending',
            'due_date' => now()->addDays(30),
        ]);
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
