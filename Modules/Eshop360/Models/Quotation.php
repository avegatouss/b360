<?php

namespace Modules\Eshop360\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class Quotation extends Model
{
    use HasFactory, BelongsToInstance, BelongsToChannel;

    protected $table = 'eshop_quotations';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'customer_id',
        'quotation_number',
        'status',
        'total',
        'notes',
        'valid_until',
        'created_by',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'valid_until' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
