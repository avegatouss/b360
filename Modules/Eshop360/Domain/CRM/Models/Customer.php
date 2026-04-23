<?php

namespace Modules\Eshop360\Domain\CRM\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Eshop360\Database\Traits\BelongsToChannel;
use Modules\Eshop360\Database\Traits\ScopedByUserAssignment;
// R-101 S2 — relations vers modèles hors-CRM : alias transitoires,
// remplacés par FQN canoniques aux sous-lots S8 (Order), S9 (Invoice),
// S11 (Communication pour SupportTicket).
use Modules\Eshop360\Models\Invoice;
use Modules\Eshop360\Models\OnlineOrder;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Models\SupportTicket;

class Customer extends Model
{
    use BelongsToChannel, BelongsToInstance, HasFactory, ScopedByUserAssignment;

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

    /**
     * Scope: customers visible to a given channel.
     * A channel must never inherit Saphir Plus customers by default.
     */
    public function scopeVisibleToChannel($query, int $channelId)
    {
        return $query->where('channel_id', $channelId);
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
