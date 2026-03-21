<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportCost extends Model
{
    use HasFactory;

    protected $table = 'eshop_import_costs';

    protected $fillable = [
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
