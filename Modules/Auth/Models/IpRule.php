<?php

namespace Modules\Auth\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Traits\BelongsToInstance;

class IpRule extends Model
{
    use BelongsToInstance;

    protected $table = 'ip_rules';

    protected $fillable = [
        'ip_address',
        'type',
        'user_id',
        'note',
        'created_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeAllowed($query)
    {
        return $query->where('type', 'allow');
    }

    public function scopeDenied($query)
    {
        return $query->where('type', 'deny');
    }
}
