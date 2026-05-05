<?php

namespace Modules\Eshop360\Console;

use App\Instances\Instance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Modules\Eshop360\Domain\Finance\Models\Invoice;
use Modules\Eshop360\Domain\Finance\Models\InvoiceItem;

class RecurringInvoiceCommand extends Command
{
    protected $signature = 'eshop360:recurring-invoices';

    protected $description = 'Generate invoices from recurring invoice templates';

    public function handle(): int
    {
        if (! $this->isRecurringSchemaReady()) {
            $this->warn('Recurring invoices skipped: schema for recurring invoices is not implemented.');

            return self::SUCCESS;
        }

        $instances = Instance::all();
        $generated = 0;

        foreach ($instances as $instance) {
            $recurringInvoices = Invoice::where('instance_id', $instance->id)
                ->where('is_recurring', true)
                ->where('recurring_status', 'active')
                ->where(function ($q) {
                    $q->whereNull('next_recurring_date')
                        ->orWhere('next_recurring_date', '<=', now());
                })
                ->with('items')
                ->get();

            foreach ($recurringInvoices as $template) {
                $newInvoice = Invoice::create([
                    'instance_id' => $instance->id,
                    'customer_id' => $template->customer_id,
                    'order_id' => $template->order_id,
                    'invoice_number' => $this->generateInvoiceNumber($instance),
                    'subtotal' => $template->subtotal,
                    'tax_amount' => $template->tax_amount,
                    'discount_amount' => $template->discount_amount,
                    'total' => $template->total,
                    'status' => 'unpaid',
                    'due_date' => now()->addDays(config('eshop360.invoice.due_days', 7)),
                    'paid_amount' => 0,
                    'due_amount' => $template->total,
                    'notes' => $template->notes,
                    'is_recurring' => false,
                    'parent_invoice_id' => $template->id,
                    'created_by' => $template->created_by,
                ]);

                foreach ($template->items as $item) {
                    InvoiceItem::create([
                        'invoice_id' => $newInvoice->id,
                        'product_id' => $item->product_id,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount' => $item->discount ?? 0,
                        'tax' => $item->tax ?? 0,
                        'total' => $item->total,
                    ]);
                }

                // Update next recurring date
                $nextDate = $this->calculateNextDate($template);
                $template->update(['next_recurring_date' => $nextDate]);

                $generated++;
                $this->info("Generated invoice {$newInvoice->reference} from template {$template->reference}");
            }
        }

        $this->info("Total recurring invoices generated: {$generated}");

        return self::SUCCESS;
    }

    private function generateInvoiceNumber($instance): string
    {
        $prefix = config('eshop360.invoice.prefix', 'INV-');
        $count = Invoice::where('instance_id', $instance->id)->count() + 1;

        return $prefix.str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    private function isRecurringSchemaReady(): bool
    {
        return Schema::hasColumns('eshop_invoices', [
            'invoice_number',
            'is_recurring',
            'recurring_status',
            'next_recurring_date',
            'parent_invoice_id',
        ]);
    }

    private function calculateNextDate(Invoice $template): \Carbon\Carbon
    {
        $interval = $template->recurring_interval ?? 'monthly';

        return match ($interval) {
            'weekly' => now()->addWeek(),
            'biweekly' => now()->addWeeks(2),
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            'yearly' => now()->addYear(),
            default => now()->addMonth(),
        };
    }
}
