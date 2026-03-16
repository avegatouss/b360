<?php

namespace Modules\Eshop360\Services;

use Modules\Eshop360\Models\AuditLog;

class AuditService
{
    public function log(string $action, string $model, ?int $modelId = null, ?array $oldValues = null, ?array $newValues = null): AuditLog
    {
        return AuditLog::create([
            'instance_id' => app(\Modules\Core\Support\CurrentInstance::class)->get()?->id,
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
