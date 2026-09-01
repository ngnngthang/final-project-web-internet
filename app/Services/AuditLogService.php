<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogService
{
    public function log(string $action, string $entityType, ?int $entityId, int $schoolId, ?int $userId, array $changes = []): void
    {
        AuditLog::create([
            'school_id' => $schoolId,
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'changes' => $changes,
        ]);
    }
}
