<?php

namespace Modules\Eshop360\Console\Commands;

use Illuminate\Console\Command;
use Modules\Eshop360\Models\InvoiceItem;
use Modules\Eshop360\Models\Invoice;
use Modules\Eshop360\Models\RecurringInvoice;
use Modules\Eshop360\Services\InvoiceService;

class GenerateRecurringInvoices extends Command
{
    protected $signature = 'eshop:generate-recurring-invoices';
    protected $description = 'Generate invoices from active recurring invoice schedules';

    public function handle(InvoiceService $invoiceService): int
    {
        $dueRecurrings = RecurringInvoice::where('is_active', true)
            ->where('next_due_date', '<=', now()->toDateString())
            ->with(['templateInvoice.items', 'customer'])
            ->get();

        if ($dueRecurrings->isEmpty()) {
            $this->info('No recurring invoices due today.');
            return self::SUCCESS;
        }

        $generated = 0;

        foreach ($dueRecurrings as $recurring) {
            $template = $recurring->templateInvoice;

            if (!$template) {
                $this->warn("Recurring #{$recurring->id}: template invoice missing, skipping.");
                continue;
            }

            // Clone the template invoice items
            $items = $template->items->map(fn (InvoiceItem $item) => [
                'product_id'  => $item->product_id,
                'description' => $item->description,
                'quantity'    => $item->quantity,
                'unit_price'  => $item->unit_price,
                'discount'    => $item->discount,
                'tax'         => $item->tax,
                'total'       => $item->total,
            ])->all();

            $newInvoice = $invoiceService->createFromItems($items, [
                'instance_id'     => $recurring->instance_id,
                'customer_id'     => $recurring->customer_id,
                'status'          => 'unpaid',
                'due_date'        => now()->addDays(30),
                'discount_amount' => $template->discount_amount,
                'notes'           => $template->notes,
                'terms'           => $template->terms,
                'footer_text'     => $template->footer_text,
                'template'        => $template->template,
                'created_by'      => $template->created_by,
            ]);

            $recurring->update([
                'next_due_date'     => $recurring->calculateNextDueDate(),
                'last_generated_at' => now(),
                'total_generated'   => $recurring->total_generated + 1,
            ]);

            $generated++;
            $this->info("Generated invoice {$newInvoice->invoice_number} from recurring #{$recurring->id}");
        }

        $this->info("Total generated: {$generated}");
        return self::SUCCESS;
    }
}
