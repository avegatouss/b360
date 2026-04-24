<?php

namespace Modules\Eshop360\Domain\Projects\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;
use Modules\Eshop360\Database\Traits\BelongsToChannel;
use Modules\Eshop360\Models\Customer;
use Modules\Eshop360\Models\Invoice;
use Modules\Eshop360\Models\Order;

class Project extends Model
{
    use BelongsToChannel, BelongsToInstance;

    protected $morphClass = \Modules\Eshop360\Models\Project::class;

    protected $table = 'eshop_projects';

    protected $fillable = [
        'channel_id',
        'instance_id', 'name', 'description', 'status', 'priority',
        'start_date', 'end_date', 'actual_end_date', 'budget', 'spent',
        'manager_id', 'customer_id', 'progress', 'settings', 'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_end_date' => 'date',
        'budget' => 'decimal:2',
        'spent' => 'decimal:2',
        'progress' => 'integer',
        'settings' => 'array',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function completedTasksCount(): int
    {
        return $this->tasks()->where('status', 'done')->count();
    }

    public function totalTasksCount(): int
    {
        return $this->tasks()->count();
    }

    public function updateProgress(): void
    {
        $total = $this->totalTasksCount();
        if ($total === 0) {
            return;
        }

        $completed = $this->completedTasksCount();
        $this->update(['progress' => (int) round(($completed / $total) * 100)]);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
            ->where('end_date', '<', now());
    }

    public static array $statuses = ['active', 'on_hold', 'completed', 'cancelled'];

    public static array $priorities = ['low', 'medium', 'high', 'urgent'];
}
