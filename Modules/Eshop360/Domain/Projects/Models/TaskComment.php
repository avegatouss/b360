<?php

namespace Modules\Eshop360\Domain\Projects\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Modules\Eshop360\Database\Traits\BelongsToChannel;

class TaskComment extends Model
{
    use BelongsToChannel;

    protected $table = 'eshop_task_comments';

    protected $fillable = ['channel_id', 'task_id', 'user_id', 'content'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
