<?php

namespace Modules\Eshop360\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Eshop360\Database\Traits\BelongsToChannel;
use Modules\Eshop360\Models\Customer;

class RecurringInvoice extends Model
{
    use BelongsToChannel, BelongsToInstance;

    protected $table = 'eshop_recurring_invoices';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'customer_id',
        'template_invoice_id',
        'frequency',
        'next_due_date',
        'last_generated_at',
        'is_active',
        'total_generated',
        'notes',
    ];

    protected $casts = [
        'next_due_date' => 'date',
        'last_generated_at' => 'datetime',
        'is_active' => 'boolean',
        'total_generated' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function templateInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'template_invoice_id');
    }

    public function calculateNextDueDate(): \Carbon\Carbon
    {
        $from = $this->next_due_date ?? now();

        return match ($this->frequency) {
            'weekly' => $from->copy()->addWeek(),
            'biweekly' => $from->copy()->addWeeks(2),
            'monthly' => $from->copy()->addMonth(),
            'quarterly' => $from->copy()->addMonths(3),
            'yearly' => $from->copy()->addYear(),
            default => $from->copy()->addMonth(),
        };
    }

    public static array $frequencies = ['weekly', 'biweekly', 'monthly', 'quarterly', 'yearly'];

    public static array $frequencyLabels = [
        'weekly' => 'Hebdomadaire',
        'biweekly' => 'Bimensuel',
        'monthly' => 'Mensuel',
        'quarterly' => 'Trimestriel',
        'yearly' => 'Annuel',
    ];
}
