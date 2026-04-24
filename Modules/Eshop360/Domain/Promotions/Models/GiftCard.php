<?php

namespace Modules\Eshop360\Domain\Promotions\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Eshop360\Database\Traits\BelongsToChannel;
use Modules\Eshop360\Models\Customer;

class GiftCard extends Model
{
    use BelongsToChannel, BelongsToInstance, HasFactory;

    protected $table = 'eshop_gift_cards';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'code',
        'customer_id',
        'customer_name',
        'batch_id',
        'amount',
        'balance',
        'status',
        'expiry_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function topups(): HasMany
    {
        return $this->hasMany(GiftCardTopup::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where(fn (Builder $q) => $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()));
    }

    public function getUsedAmountAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->balance);
    }

    public function getUsedPercentAttribute(): int
    {
        return $this->amount > 0 ? (int) round(($this->used_amount / $this->amount) * 100) : 0;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getOwnerNameAttribute(): string
    {
        return $this->customer?->name ?? $this->customer_name ?? '—';
    }

    /**
     * Generate a code based on format settings.
     */
    public static function generateCode(array $settings = []): string
    {
        $prefix = strtoupper($settings['prefix'] ?? 'GC');
        $length = max(4, min(20, (int) ($settings['length'] ?? 8)));
        $separator = $settings['separator'] ?? '-';
        $groupSize = max(0, (int) ($settings['group_size'] ?? 4));
        $charset = $settings['charset'] ?? 'alphanumeric';

        $chars = match ($charset) {
            'numeric' => '0123456789',
            'alpha' => 'ABCDEFGHJKLMNPQRSTUVWXYZ',
            default => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789',
        };

        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }

        if ($groupSize > 0 && $length > $groupSize) {
            $code = implode($separator, str_split($code, $groupSize));
        }

        return $prefix.$separator.$code;
    }
}
