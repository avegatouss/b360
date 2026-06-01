<?php

namespace Modules\Currency\Models;

use Illuminate\Database\Eloquent\Model;

class UserCurrencyPreference extends Model
{
    protected $table = 'user_currency_preferences';

    protected $fillable = [
        'user_id',
        'instance_id',
        'preferred_currency',
    ];
}
