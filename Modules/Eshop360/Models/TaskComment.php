<?php

namespace Modules\Eshop360\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskComment extends Model
{
    protected $table = 'eshop_task_comments';

    protected $fillable = ['task_id', 'user_id', 'content'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
