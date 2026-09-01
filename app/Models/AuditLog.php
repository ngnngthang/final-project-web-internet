<?php

namespace App\Models;

use App\Core\Database;

class AuditLog
{
    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO audit_logs (school_id, user_id, action, entity_type, entity_id, changes, ip_address, created_at)
             VALUES (:school_id, :user_id, :action, :entity_type, :entity_id, :changes, :ip_address, NOW())'
        );
        $stmt->execute([
            'school_id' => $data['school_id'],
            'user_id' => $data['user_id'],
            'action' => $data['action'],
            'entity_type' => $data['entity_type'],
            'entity_id' => $data['entity_id'] ?? null,
            'changes' => isset($data['changes']) ? json_encode($data['changes']) : null,
            'ip_address' => $data['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? null),
        ]);
        return (int) $pdo->lastInsertId();
    }
}
