<?php

namespace Modules\Eshop360\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Eshop360\Database\Traits\ScopedByUserAssignment;

class Customer extends Model
{
    use HasFactory, BelongsToInstance, ScopedByUserAssignment;

    protected static array $userAssignmentConfig = [
        ['type' => 'customer', 'column' => 'id'],
    ];

    protected $table = 'eshop_customers';

    protected $fillable = [
        'instance_id',
        'channel_id',
        'user_id',
        'group_id',
        'store_id',
        'support_team_id',
        'code',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'wallet_balance',
        'credit_limit',
        'date_of_birth',
        'tax_number',
        'company_name',
        'notes',
        'loyalty_points',
        'bonus_points',
        'is_active',
    ];

    protected $casts = [
        'wallet_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'date_of_birth' => 'date',
        'loyalty_points' => 'integer',
        'bonus_points' => 'integer',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(DistributionChannel::class, 'channel_id');
    }

    /**
     * Scope: customers visible to a given channel (channel's own + shared).
     */
    public function scopeVisibleToChannel($query, int $channelId)
    {
        return $query->where(function ($q) use ($channelId) {
            $q->where('channel_id', $channelId)
              ->orWhereNull('channel_id');
        });
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class, 'group_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CustomerTransaction::class);
    }

    public function dues(): HasMany
    {
        return $this->hasMany(CustomerDue::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function onlineOrders(): HasMany
    {
        return $this->hasMany(OnlineOrder::class);
    }
}
