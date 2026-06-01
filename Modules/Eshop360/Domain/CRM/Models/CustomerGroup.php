<?php

namespace Modules\Eshop360\Domain\CRM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class CustomerGroup extends Model
{
    use BelongsToChannel, BelongsToInstance, HasFactory;

    protected $table = 'eshop_customer_groups';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'name',
        'discount_rate',
    ];

    protected $casts = [
        'discount_rate' => 'decimal:2',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'group_id');
    }
}
