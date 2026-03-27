<?php

namespace Modules\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupLog extends Model
{
    public $timestamps = false;

    protected $table = 'backup_logs';

    protected $fillable = [
        'filename',
        'size_bytes',
        'type',
        'status',
        'notes',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'created_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Format file size for display.
     */
    public function formattedSize(): string
    {
        $bytes = $this->size_bytes;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' Go';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' Mo';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' Ko';
        }
        return $bytes . ' octets';
    }
}
