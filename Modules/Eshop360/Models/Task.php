<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Traits\BelongsToInstance;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class Task extends Model
{
    use BelongsToChannel, BelongsToInstance;

    protected $table = 'eshop_tasks';

    protected $fillable = [
        'channel_id',
        'instance_id', 'project_id', 'parent_task_id', 'title', 'description',
        'status', 'priority', 'assigned_to', 'created_by',
        'start_date', 'due_date', 'completed_at',
        'estimated_hours', 'actual_hours', 'sort_order', 'labels',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'labels' => 'array',
        'estimated_hours' => 'integer',
        'actual_hours' => 'integer',
        'sort_order' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function subTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function markDone(): void
    {
        $this->update(['status' => 'done', 'completed_at' => now()]);
        $this->project->updateProgress();
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'done';
    }

    public static array $statuses = ['todo', 'in_progress', 'review', 'done', 'cancelled'];

    public static array $priorities = ['low', 'medium', 'high', 'urgent'];
}
