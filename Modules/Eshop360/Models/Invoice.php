<?php

namespace Modules\Eshop360\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Database\Traits\BelongsToInstance;

class Invoice extends Model
{
    use HasFactory, BelongsToInstance, SoftDeletes;

    protected $table = 'eshop_invoices';

    protected $fillable = [
        'instance_id',
        'order_id',
        'customer_id',
        'project_id',
        'invoice_number',
        'status',
        'due_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total',
        'tax_rate',
        'paid_amount',
        'due_amount',
        'notes',
        'terms',
        'footer_text',
        'template',
        'payment_token',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getReferenceAttribute(): string
    {
        return (string) ($this->invoice_number ?? ('INV-' . ($this->id ?? 'NEW')));
    }

    public function getShippingAmountAttribute(): float
    {
        if (array_key_exists('shipping_amount', $this->attributes)) {
            return (float) $this->attributes['shipping_amount'];
        }

        if ($this->relationLoaded('order')) {
            return (float) ($this->order?->shipping_amount ?? 0);
        }

        return (float) ($this->order()->value('shipping_amount') ?? 0);
    }
}
