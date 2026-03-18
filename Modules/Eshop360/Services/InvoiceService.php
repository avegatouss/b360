<?php

namespace Modules\Eshop360\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Invoice;
use Modules\Eshop360\Models\InvoiceItem;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Product;

class InvoiceService
{
    /**
     * Create an invoice from an existing order.
     */
    public function createFromOrder(Order $order): Invoice
    {
        $lineDiscountAmount = (float) $order->items()->sum('discount');

        return $this->createFromItems(
            $order->items->map(fn ($orderItem): array => [
                'product_id' => $orderItem->product_id,
                'description' => $orderItem->product_name,
                'quantity' => $orderItem->quantity,
                'unit_price' => $orderItem->unit_price,
                'discount' => $orderItem->discount,
                'tax' => $orderItem->tax,
                'total' => $orderItem->total,
            ])->all(),
            [
                'instance_id' => $order->instance_id,
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'status' => 'unpaid',
                'due_date' => now()->addDays(30),
                'discount_amount' => max(0, (float) $order->discount_amount - $lineDiscountAmount),
                'created_by' => auth()->id(),
            ],
        );
    }

    /**
     * Create an invoice from normalized line items.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, mixed>  $invoiceData
     */
    public function createFromItems(array $items, array $invoiceData): Invoice
    {
        $instance = CurrentInstance::get();

        return DB::transaction(function () use ($items, $invoiceData, $instance) {
            $normalizedItems = array_map(fn (array $item): array => $this->normalizeItem($item), $items);

            $subtotal = collect($normalizedItems)->sum('line_subtotal');
            $taxAmount = collect($normalizedItems)->sum('line_tax');
            $lineDiscountAmount = collect($normalizedItems)->sum('line_discount');
            $orderLevelDiscount = (float) ($invoiceData['discount_amount'] ?? 0);
            $totalDiscount = round($lineDiscountAmount + $orderLevelDiscount, 2);
            $total = round($subtotal + $taxAmount - $totalDiscount, 2);
            $targetPaidAmount = round((float) ($invoiceData['paid_amount'] ?? 0), 2);

            $invoice = Invoice::create([
                'instance_id' => $invoiceData['instance_id'] ?? $instance?->id,
                'order_id' => $invoiceData['order_id'] ?? null,
                'customer_id' => $invoiceData['customer_id'] ?? null,
                'invoice_number' => $invoiceData['invoice_number'] ?? $this->generateInvoiceNumber(),
                'status' => $invoiceData['status'] ?? 'draft',
                'due_date' => $invoiceData['due_date'] ?? null,
                'subtotal' => round($subtotal, 2),
                'tax_amount' => round($taxAmount, 2),
                'discount_amount' => $totalDiscount,
                'total' => $total,
                'paid_amount' => $targetPaidAmount,
                'due_amount' => round(max(0, $total - $targetPaidAmount), 2),
                'notes' => $invoiceData['notes'] ?? null,
                'terms' => $invoiceData['terms'] ?? null,
                'footer_text' => $invoiceData['footer_text'] ?? null,
                'template' => $invoiceData['template'] ?? 'default',
                'created_by' => $invoiceData['created_by'] ?? auth()->id(),
            ]);

            foreach ($normalizedItems as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['line_discount'],
                    'tax' => $item['line_tax'],
                    'total' => $item['line_total'],
                ]);
            }

            if ($targetPaidAmount !== 0.0) {
                $this->syncPaidAmount(
                    $invoice,
                    $targetPaidAmount,
                    (string) ($invoiceData['payment_method'] ?? 'cash'),
                    'INV-PAY'
                );
            } elseif (($invoiceData['status'] ?? 'draft') !== 'draft') {
                $this->calculateTotals($invoice);
            }

            return $invoice->fresh(['items', 'payments']);
        });
    }

    /**
     * Generate a unique invoice number (e.g. INV-20260312-X7Y8Z9).
     */
    public function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $random = Str::upper(Str::random(6));

        $number = "{$prefix}-{$date}-{$random}";

        while (Invoice::where('invoice_number', $number)->exists()) {
            $random = Str::upper(Str::random(6));
            $number = "{$prefix}-{$date}-{$random}";
        }

        return $number;
    }

    /**
     * Mark an invoice as paid (fully or partially).
     */
    public function markAsPaid(Invoice $invoice, float $amount, string $method): void
    {
        $currentPaidAmount = (float) $invoice->payments()
            ->where('status', 'completed')
            ->sum('amount');

        $this->syncPaidAmount($invoice, $currentPaidAmount + $amount, $method, 'INV-PAY');
    }

    /**
     * Synchronize an invoice to a target paid amount by creating a delta payment.
     */
    public function syncPaidAmount(
        Invoice $invoice,
        float $targetPaidAmount,
        ?string $method = null,
        string $referencePrefix = 'INV-ADJ',
        ?string $notes = null,
    ): ?\Modules\Eshop360\Models\Payment {
        $currentPaidAmount = (float) $invoice->payments()
            ->where('status', 'completed')
            ->sum('amount');

        $delta = round($targetPaidAmount - $currentPaidAmount, 2);

        if ($delta === 0.0) {
            $this->calculateTotals($invoice);

            return null;
        }

        $instance = CurrentInstance::get();

        $payment = $invoice->payments()->create([
            'instance_id' => $invoice->instance_id ?? $instance?->id,
            'amount' => $delta,
            'method' => $method ?? 'cash',
            'reference' => $referencePrefix . '-' . $invoice->id . '-' . ($invoice->payments()->count() + 1),
            'status' => 'completed',
            'notes' => $notes ?? 'Payment adjustment',
            'received_by' => auth()->id(),
        ]);

        $this->calculateTotals($invoice);

        return $payment;
    }

    /**
     * Recalculate paid/due amounts and update invoice status.
     */
    public function calculateTotals(Invoice $invoice): void
    {
        $paidAmount = (float) $invoice->payments()
            ->where('status', 'completed')
            ->sum('amount');

        $dueAmount = (float) $invoice->total - $paidAmount;

        $invoice->update([
            'paid_amount' => round($paidAmount, 2),
            'due_amount'  => round(max(0, $dueAmount), 2),
            'status'      => $this->resolveStatus($paidAmount, (float) $invoice->total),
        ]);
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{
     *     product_id: int|null,
     *     description: string,
     *     quantity: int,
     *     unit_price: float,
     *     line_subtotal: float,
     *     line_discount: float,
     *     line_tax: float,
     *     line_total: float
     * }
     */
    private function normalizeItem(array $item): array
    {
        $productId = isset($item['product_id']) ? (int) $item['product_id'] : null;
        $product = $productId ? Product::find($productId) : null;
        $quantity = max(1, (int) ($item['quantity'] ?? 1));
        $unitPrice = round((float) ($item['unit_price'] ?? $product?->price ?? 0), 2);
        $lineSubtotal = round($unitPrice * $quantity, 2);
        $lineDiscount = round((float) ($item['discount'] ?? 0), 2);
        $taxRate = (float) ($item['tax_rate'] ?? $product?->tax_rate ?? 0);
        $lineTax = array_key_exists('tax', $item)
            ? round((float) $item['tax'], 2)
            : round(max(0, $lineSubtotal - $lineDiscount) * ($taxRate / 100), 2);
        $lineTotal = array_key_exists('total', $item)
            ? round((float) $item['total'], 2)
            : round(max(0, $lineSubtotal - $lineDiscount) + $lineTax, 2);

        return [
            'product_id' => $productId,
            'description' => (string) ($item['description'] ?? $item['product_name'] ?? $product?->name ?? 'Ligne sans description'),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_subtotal' => $lineSubtotal,
            'line_discount' => $lineDiscount,
            'line_tax' => $lineTax,
            'line_total' => $lineTotal,
        ];
    }

    private function resolveStatus(float $paidAmount, float $totalAmount): string
    {
        if ($paidAmount >= $totalAmount && $totalAmount > 0) {
            return 'paid';
        }

        if ($paidAmount > 0) {
            return 'partial';
        }

        return 'unpaid';
    }
}
