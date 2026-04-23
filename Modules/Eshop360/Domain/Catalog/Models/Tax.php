<?php

namespace Modules\Eshop360\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Traits\BelongsToInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class Tax extends Model
{
    use BelongsToChannel, BelongsToInstance, HasFactory;

    protected $table = 'eshop_taxes';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'name',
        'rate',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'is_active' => 'boolean',
    ];
}
