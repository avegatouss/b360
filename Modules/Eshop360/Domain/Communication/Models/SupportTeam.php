<?php

namespace Modules\Eshop360\Domain\Communication\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Eshop360\Database\Traits\BelongsToChannel;
use Modules\Eshop360\Domain\CRM\Models\Customer;

class SupportTeam extends Model
{
    use BelongsToChannel, BelongsToInstance;

    protected $table = 'eshop_support_teams';

    protected $fillable = [
        'channel_id',
        'instance_id',
        'name',
        'description',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'eshop_support_team_members', 'team_id', 'user_id')
            ->withTimestamps();
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'support_team_id');
    }

    /**
     * Get a random member user ID for message routing.
     */
    public function getRecipientUserId(): ?int
    {
        return $this->members()->inRandomOrder()->value('users.id');
    }

    /**
     * Get all member user IDs.
     */
    public function getMemberIds(): array
    {
        return $this->members()->pluck('users.id')->toArray();
    }

    /**
     * Get the default team for an instance.
     */
    public static function getDefault(int $instanceId): ?self
    {
        return static::where('instance_id', $instanceId)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }
}
