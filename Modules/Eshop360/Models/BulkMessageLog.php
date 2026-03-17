<?php

namespace Modules\Eshop360\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Traits\BelongsToInstance;

class BulkMessageLog extends Model
{
    use HasFactory, BelongsToInstance;

    protected $table = 'eshop_bulk_message_logs';

    protected $fillable = [
        'instance_id',
        'type',
        'subject',
        'message',
        'template_id',
        'filters',
        'total_recipients',
        'sent_count',
        'failed_count',
        'status',
        'created_by',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }
}
