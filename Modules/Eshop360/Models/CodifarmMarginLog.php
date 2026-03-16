<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Traits\BelongsToInstance;

class CodifarmMarginLog extends Model
{
    use HasFactory, BelongsToInstance;

    protected $table = 'eshop_codifarm_margin_logs';

    protected $fillable = [
        'instance_id',
        'order_id',
        'total_margin',
        'debt_part',
        'codifarm_part',
        'saphir_part',
    ];

    protected $casts = [
        'total_margin' => 'decimal:2',
        'debt_part' => 'decimal:2',
        'codifarm_part' => 'decimal:2',
        'saphir_part' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
