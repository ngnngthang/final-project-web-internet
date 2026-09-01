<?php

namespace App\Models;

use App\Core\Database;

class Lop
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM lop WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function forKhoi(int $khoiId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM lop WHERE khoi_id = ? ORDER BY name');
        $stmt->execute([$khoiId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO lop (khoi_id, school_id, teacher_id, name, max_capacity, current_enrollment, status, created_at, updated_at)
             VALUES (:khoi_id, :school_id, :teacher_id, :name, :max_capacity, 0, :status, NOW(), NOW())'
        );
        $stmt->execute([
            'khoi_id' => $data['khoi_id'],
            'school_id' => $data['school_id'],
            'teacher_id' => $data['teacher_id'] ?? null,
            'name' => $data['name'],
            'max_capacity' => $data['max_capacity'] ?? 50,
            'status' => $data['status'] ?? 'Planning',
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function incrementEnrollment(int $lopId): void
    {
        $stmt = Database::connection()->prepare('UPDATE lop SET current_enrollment = current_enrollment + 1 WHERE id = ?');
        $stmt->execute([$lopId]);
    }
}
