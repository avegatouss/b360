<?php

declare(strict_types=1);

namespace Modules\Couture360\Domain\Auth\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Opaque per-device API token for the Couture360 mobile app.
 *
 * Auth-infra model (NOT a business model): intentionally does NOT use
 * BelongsToInstance, because it must be looked up by token hash BEFORE any
 * instance context exists. The bound instance is carried in `instance_id`.
 *
 * @property int $id
 * @property int $instance_id
 * @property int $user_id
 * @property string $name
 * @property string $token_hash
 * @property \Illuminate\Support\Carbon|null $last_used_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class CoutureDeviceToken extends Model
{
    protected $table = 'cout_device_tokens';

    protected $fillable = [
        'instance_id',
        'user_id',
        'name',
        'token_hash',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}
