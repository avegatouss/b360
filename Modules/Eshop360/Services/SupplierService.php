<?php

namespace Modules\Eshop360\Services;

use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Models\PurchaseOrder;
use Modules\Eshop360\Models\Supplier;

class SupplierService
{
    /**
     * Get supplier balance (what we owe them)
     */
    public function getSupplierBalance(Supplier $supplier): float
    {
        return PurchaseOrder::where('supplier_id', $supplier->id)
            ->where('status', '!=', 'cancelled')
            ->sum('due_amount');
    }

    /**
     * Get supplier purchase history with totals
     */
    public function getPurchaseHistory(Supplier $supplier, ?string $from = null, ?string $to = null): array
    {
        $query = PurchaseOrder::where('supplier_id', $supplier->id)
            ->where('status', '!=', 'cancelled');

        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        $orders = $query->latest()->get();

        return [
            'orders' => $orders,
            'total_purchases' => $orders->sum('total'),
            'total_paid' => $orders->sum('paid_amount'),
            'total_due' => $orders->sum('due_amount'),
            'count' => $orders->count(),
        ];
    }

    /**
     * Record payment to supplier
     */
    public function recordPayment(PurchaseOrder $order, float $amount, string $method, ?string $reference = null): void
    {
        DB::transaction(function () use ($order, $amount, $method, $reference) {
            $paidAmount = round((float) $order->paid_amount + $amount, 2);
            $dueAmount = round(max(0, (float) $order->total - $paidAmount), 2);

            $order->update([
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'payment_status' => $dueAmount <= 0 ? 'paid' : 'partial',
            ]);

            // Record payment via polymorphic payments
            $order->payments()->create([
                'instance_id' => $order->instance_id,
                'amount' => $amount,
                'method' => $method,
                'reference' => $reference ?: 'PO-PAY-'.$order->id.'-'.now()->format('YmdHis'),
                'status' => 'completed',
                'received_by' => auth()->id(),
            ]);
        });
    }
}
