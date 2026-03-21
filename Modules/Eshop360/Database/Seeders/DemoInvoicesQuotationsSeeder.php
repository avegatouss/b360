<?php

namespace Modules\Eshop360\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Support\CurrentInstance;
use Modules\Eshop360\Models\Invoice;
use Modules\Eshop360\Models\InvoiceItem;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\Quotation;
use Modules\Eshop360\Models\QuotationItem;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Product;

class DemoInvoicesQuotationsSeeder extends Seeder
{
    public function run(?int $instanceId = null): void
    {
        $instanceId = $instanceId ?? CurrentInstance::get()?->id ?? 1;

        // Skip if already seeded
        if (Invoice::where('instance_id', $instanceId)->exists()) {
            $this->command?->info('Invoices already exist — skipping.');
            return;
        }

        $this->command?->info('Creating demo invoices from existing orders...');

        // Create invoices for completed orders
        $completedOrders = Order::where('instance_id', $instanceId)
            ->where('status', 'completed')
            ->with('items', 'customer')
            ->limit(30)
            ->get();

        $invoiceCount = 0;
        foreach ($completedOrders as $order) {
            $isPaid = $order->payment_status === 'paid';
            $isPartial = $order->payment_status === 'partial';
            $paidAmount = $isPaid ? $order->total : ($isPartial ? round($order->total * rand(30, 70) / 100, 0) : 0);
            $dueAmount = max(0, (float) $order->total - $paidAmount);

            // Valid statuses: draft, sent, paid, unpaid, overdue, cancelled
            $status = $isPaid ? 'paid' : 'unpaid';
            if (! $isPaid && $invoiceCount % 4 === 0) {
                $status = 'overdue';
            }
            if (! $isPaid && $invoiceCount % 7 === 0) {
                $status = 'sent';
            }

            $invoice = Invoice::create([
                'instance_id'     => $instanceId,
                'order_id'        => $order->id,
                'customer_id'     => $order->customer_id,
                'invoice_number'  => 'INV-' . now()->format('Y') . '-' . str_pad($invoiceCount + 1, 5, '0', STR_PAD_LEFT),
                'status'          => $status,
                'due_date'        => $isPaid ? null : now()->subDays(rand(-30, 15)),
                'subtotal'        => $order->subtotal,
                'tax_amount'      => $order->tax_amount ?? 0,
                'discount_amount' => $order->discount_amount ?? 0,
                'total'           => $order->total,
                'paid_amount'     => $paidAmount,
                'due_amount'      => $dueAmount,
                'notes'           => "Facture generee depuis commande #{$order->order_number}",
                'created_by'      => 1,
                'template'        => collect(['default', 'modern', 'classic'])->random(),
            ]);

            foreach ($order->items as $item) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $item->product_id,
                    'description' => $item->product_name ?? $item->product?->name ?? 'Article',
                    'quantity'    => $item->quantity,
                    'unit_price'  => $item->unit_price,
                    'discount'    => $item->discount ?? 0,
                    'tax'         => $item->tax ?? 0,
                    'total'       => $item->total,
                ]);
            }

            // Create payment records for paid/partial
            if ($paidAmount > 0) {
                $invoice->payments()->create([
                    'instance_id' => $instanceId,
                    'amount'      => $paidAmount,
                    'method'      => collect(['cash', 'card', 'bank_transfer', 'cheque'])->random(),
                    'reference'   => 'INV-PAY-' . $invoice->id . '-1',
                    'status'      => 'completed',
                    'notes'       => 'Paiement initial',
                    'received_by' => 1,
                ]);
            }

            $invoiceCount++;
        }

        $this->command?->info("Created {$invoiceCount} invoices.");

        // Create quotations
        $this->command?->info('Creating demo quotations...');

        $customers = Customer::where('instance_id', $instanceId)->where('is_active', true)->get();
        $products = Product::where('instance_id', $instanceId)->where('is_active', true)->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            $this->command?->warn('No customers or products — skipping quotations.');
            return;
        }

        $quotationStatuses = ['draft', 'sent', 'pending', 'ordered', 'cancelled'];
        $quotationCount = 0;

        for ($i = 0; $i < 12; $i++) {
            $customer = $customers->random();
            $status = $quotationStatuses[array_rand($quotationStatuses)];
            $itemCount = rand(2, 5);
            $selectedProducts = $products->random(min($itemCount, $products->count()));
            $total = 0;

            $quotation = Quotation::create([
                'instance_id'      => $instanceId,
                'customer_id'      => $customer->id,
                'quotation_number' => 'DEV-' . now()->format('Y') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'status'           => $status,
                'total'            => 0,
                'notes'            => collect([null, 'Devis valable 30 jours', 'Livraison sous 5 jours ouvrables', 'Conditions speciales'])->random(),
                'valid_until'      => $status === 'cancelled' ? now()->subDays(rand(1, 30)) : now()->addDays(rand(15, 60)),
                'created_by'       => 1,
            ]);

            foreach ($selectedProducts as $product) {
                $qty = rand(1, 10);
                $price = (float) $product->price;
                $lineTotal = $qty * $price;
                $total += $lineTotal;

                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id'   => $product->id,
                    'quantity'     => $qty,
                    'unit_price'   => $price,
                    'total'        => $lineTotal,
                ]);
            }

            $quotation->update(['total' => $total]);
            $quotationCount++;
        }

        $this->command?->info("Created {$quotationCount} quotations.");
    }
}
