<?php

use App\Models\AuditLog;

/**
 * Convenience wrapper around AuditLog::create() used by Services and
 * Controllers — mirrors the AuditLogService but as a plain function since
 * this project has no DI container.
 */
function app_audit_log(string $action, string $entityType, ?int $entityId, int $schoolId, ?int $userId, array $changes = []): void
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
