<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Database\Traits\BelongsToInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class FneInvoice extends Model
{
    use BelongsToInstance, BelongsToChannel;

    protected $table = 'eshop_fne_invoices';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'invoiceable_type',
        'invoiceable_id',
        'fne_reference',
        'fne_id',
        'fne_token',
        'fne_ncc',
        'template',
        'status',
        'amount',
        'vat_amount',
        'request_payload',
        'response_payload',
        'error_message',
        'signed_by',
        'signed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'request_payload' => 'array',
        'response_payload' => 'array',
        'signed_at' => 'datetime',
    ];

    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'signed_by');
    }

    public function getQrCodeUrlAttribute(): ?string
    {
        return $this->fne_token;
    }

    public function isSigned(): bool
    {
        return $this->status === 'signed' && $this->fne_reference !== null;
    }
}
