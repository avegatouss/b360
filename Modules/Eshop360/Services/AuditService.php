<?php

namespace Modules\Eshop360\Services;

use Modules\Core\Models\AuditLog;

/**
 * Eshop360 audit service — writes to the unified Core audit_logs table
 * with source_module = 'eshop360'.
 *
 * The legacy eshop_audit_logs table is kept read-only for historical data.
 */
class AuditService
{
    public function log(string $action, string $model, ?int $modelId = null, ?array $oldValues = null, ?array $newValues = null): AuditLog
    {
        return AuditLog::create([
            'instance_id' => app(\Modules\Core\Support\CurrentInstance::class)->get()?->id,
            'source_module' => 'eshop360',
            'user_id' => auth()->id(),
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
