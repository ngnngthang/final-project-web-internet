<?php

namespace App\Models;

use App\Core\Database;

class Teacher
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM teachers WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByUserId(int $userId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM teachers WHERE user_id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO teachers (school_id, user_id, full_name, employee_id, subject, status, created_at, updated_at)
             VALUES (:school_id, :user_id, :full_name, :employee_id, :subject, :status, NOW(), NOW())'
        );
        $stmt->execute([
            'school_id' => $data['school_id'],
            'user_id' => $data['user_id'],
            'full_name' => $data['full_name'],
            'employee_id' => $data['employee_id'],
            'subject' => $data['subject'] ?? null,
            'status' => $data['status'] ?? 'Active',
        ]);
        return (int) $pdo->lastInsertId();
    }
}
