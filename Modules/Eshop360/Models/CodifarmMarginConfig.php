<?php

// DEPRECATED: Use DistributionChannel and ChannelMarginLog instead. Will be deleted after migration.

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Traits\BelongsToInstance;

class CodifarmMarginConfig extends Model
{
    use HasFactory, BelongsToInstance;

    protected $table = 'eshop_codifarm_margin_config';

    protected $fillable = [
        'instance_id',
        'saphir_margin_rate',
        'codifarm_buy_rate',
        'debt_share',
        'codifarm_share',
        'saphir_share',
    ];

    protected $casts = [
        'saphir_margin_rate' => 'decimal:4',
        'codifarm_buy_rate' => 'decimal:4',
        'debt_share' => 'decimal:4',
        'codifarm_share' => 'decimal:4',
        'saphir_share' => 'decimal:4',
    ];
}
