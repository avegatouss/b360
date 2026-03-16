<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Traits\BelongsToInstance;

class EmailTemplate extends Model
{
    use HasFactory, BelongsToInstance;

    protected $table = 'eshop_email_templates';

    protected $fillable = [
        'instance_id',
        'name',
        'subject',
        'body',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
