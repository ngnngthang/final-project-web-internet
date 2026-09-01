<?php

namespace App\Models;

use App\Core\Database;

class AssessmentType
{
    public static function forSchool(int $schoolId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM assessment_types WHERE school_id = ? AND is_enabled = 1');
        $stmt->execute([$schoolId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM assessment_types WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO assessment_types (school_id, name, weight, is_enabled, created_at, updated_at)
             VALUES (:school_id, :name, :weight, :is_enabled, NOW(), NOW())'
        );
        $stmt->execute([
            'school_id' => $data['school_id'],
            'name' => $data['name'],
            'weight' => $data['weight'],
            'is_enabled' => $data['is_enabled'] ?? true,
        ]);
        return (int) $pdo->lastInsertId();
    }
}
