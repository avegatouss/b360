<?php

namespace Modules\Eshop360\Domain\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Traits\BelongsToInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class EshopPaymentGateway extends Model
{
    use BelongsToChannel, BelongsToInstance, HasFactory;

    protected $morphClass = \Modules\Eshop360\Models\EshopPaymentGateway::class;

    protected $table = 'eshop_payment_gateways';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'driver',
        'display_name',
        'config',
        'is_active',
        'is_test_mode',
        'sort_order',
    ];

    protected $casts = [
        'config' => 'encrypted:array',
        'is_active' => 'boolean',
        'is_test_mode' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $hidden = [
        'config',
    ];

    /**
     * Get active gateways for an instance, ordered by sort_order.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Get the decrypted config array (for passing to driver).
     */
    public function getDecryptedConfig(): array
    {
        return $this->config ?? [];
    }
}
