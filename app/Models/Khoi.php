<?php

namespace App\Models;

use App\Core\Database;

class Khoi
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM khoi WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function forSchool(int $schoolId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM khoi WHERE school_id = ? ORDER BY academic_year DESC, name');
        $stmt->execute([$schoolId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO khoi (school_id, name, academic_year, is_active, created_by, created_at, updated_at)
             VALUES (:school_id, :name, :academic_year, :is_active, :created_by, NOW(), NOW())'
        );
        $stmt->execute([
            'school_id' => $data['school_id'],
            'name' => $data['name'],
            'academic_year' => $data['academic_year'],
            'is_active' => $data['is_active'] ?? true,
            'created_by' => $data['created_by'],
        ]);
        return (int) $pdo->lastInsertId();
    }
}
