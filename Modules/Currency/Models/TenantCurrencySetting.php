<?php

namespace Modules\Currency\Models;

use Illuminate\Database\Eloquent\Model;

class TenantCurrencySetting extends Model
{
    protected $table = 'tenant_currency_settings';

    protected $fillable = [
        'instance_id',
        'default_currency',
        'allowed_currencies',
        'multi_currency_enabled',
        'auto_update_rates',
        'primary_api_source',
        'fallback_api_source',
    ];

    protected $casts = [
        'allowed_currencies' => 'array',
        'multi_currency_enabled' => 'boolean',
        'auto_update_rates' => 'boolean',
    ];

    /**
     * Get or create settings for an instance with sensible defaults.
     */
    public static function forInstance(int $instanceId): self
    {
        return static::firstOrCreate(
            ['instance_id' => $instanceId],
            [
                'default_currency' => 'XOF',
                'allowed_currencies' => ['XOF'],
                'multi_currency_enabled' => false,
                'auto_update_rates' => true,
                'primary_api_source' => 'open.er-api.com',
            ]
        );
    }
}
