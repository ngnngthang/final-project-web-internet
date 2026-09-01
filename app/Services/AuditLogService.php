<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    public function log(string $action, Model $model, array $changes = []): AuditLog
    {
        return AuditLog::create([
            'school_id' => $model->school_id ?? auth()->user()?->school_id,
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => class_basename($model),
            'entity_id' => $model->id,
            'changes' => $changes,
            'ip_address' => request()?->ip(),
            'created_at' => now(),
        ]);
    }
}
