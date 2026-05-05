<?php

namespace Modules\Eshop360\Domain\Purchasing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Eshop360\Database\Traits\BelongsToChannel;
use Modules\Eshop360\Domain\Inventory\Models\Store;

class Supplier extends Model
{
    use BelongsToChannel, BelongsToInstance, HasFactory, SoftDeletes;

    protected $table = 'eshop_suppliers';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'name',
        'company',
        'country',
        'contact_person',
        'email',
        'phone',
        'address',
        'notes',
        'balance',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'balance' => 'decimal:2',
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function importOrders(): HasMany
    {
        return $this->hasMany(ImportOrder::class);
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'eshop_supplier_store');
    }
}
