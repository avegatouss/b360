<?php

namespace Modules\Eshop360\Domain\Purchasing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Eshop360\Database\Traits\BelongsToChannel;

class ImportCost extends Model
{
    use BelongsToChannel, HasFactory;

    protected $table = 'eshop_import_costs';

    protected $fillable = [
        'channel_id',
        'import_order_id',
        'type',
        'description',
        'amount',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function importOrder(): BelongsTo
    {
        return $this->belongsTo(ImportOrder::class);
    }

    public function getLabelAttribute(): string
    {
        $labels = [
            'freight' => __('Fret'), 'customs' => __('Douane'), 'tax' => __('Taxe'),
            'admin' => __('Frais admin'), 'local_transport' => __('Transport local'),
            'handling' => __('Manutention'), 'storage' => __('Entreposage'), 'other' => __('Autre'),
        ];

        return $labels[$this->type] ?? ucfirst($this->type);
    }
}
