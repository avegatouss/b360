<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class CompanyCharge extends Model
{
    use HasFactory, BelongsToInstance, BelongsToChannel;

    protected $table = 'eshop_company_charges';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'category',
        'name',
        'amount_monthly',
        'is_active',
        'start_date',
    ];

    protected $casts = [
        'amount_monthly' => 'decimal:2',
        'is_active' => 'boolean',
        'start_date' => 'date',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(ChargeLog::class, 'charge_id');
    }

    public function getCostPerSecondAttribute(): float
    {
        return (float) $this->amount_monthly / (30 * 24 * 3600);
    }
}
