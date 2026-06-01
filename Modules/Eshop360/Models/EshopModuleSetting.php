<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Model;

class EshopModuleSetting extends Model
{
    protected $table = 'eshop_module_settings';

    protected $fillable = ['instance_id', 'channel_id', 'group', 'data'];

    protected $casts = [
        'data' => 'array',
    ];
}
