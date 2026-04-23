<?php

namespace Modules\Eshop360\Domain\CRM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Eshop360\Database\Traits\BelongsToChannel;
// R-101 S2 — relations vers modèles hors-CRM : référencées via les alias
// `Modules\Eshop360\Models\*` pour transition fluide. Remplacés par leurs
// FQN canoniques au fur et à mesure des sous-lots (Invoice → S9, Order → S8).
use Modules\Eshop360\Models\Invoice;
use Modules\Eshop360\Models\Order;

class CustomerDue extends Model
{
    use BelongsToChannel, HasFactory;

    protected $table = 'eshop_customer_dues';

    protected $fillable = [
        'channel_id',
        'customer_id',
        'order_id',
        'invoice_id',
        'amount_due',
        'paid_amount',
        'due_date',
        'status',
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
