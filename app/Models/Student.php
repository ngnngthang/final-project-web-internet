<?php

namespace App\Models;

use App\Core\Database;

class Student
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM students WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByUserId(int $userId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM students WHERE user_id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO students (school_id, user_id, full_name, student_id, date_of_birth, email, phone, status, created_at, updated_at)
             VALUES (:school_id, :user_id, :full_name, :student_id, :date_of_birth, :email, :phone, :status, NOW(), NOW())'
        );
        $stmt->execute([
            'school_id' => $data['school_id'],
            'user_id' => $data['user_id'],
            'full_name' => $data['full_name'],
            'student_id' => $data['student_id'],
            'date_of_birth' => $data['date_of_birth'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'] ?? 'Active',
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function bulkImport(array $rows): array
    {
        $pdo = Database::connection();
        $results = ['inserted' => 0, 'errors' => []];

        $pdo->beginTransaction();
        try {
            foreach ($rows as $i => $row) {
                if (empty($row['full_name']) || empty($row['student_id'])) {
                    $results['errors'][] = "Row {$i}: missing required field";
                    continue;
                }
                self::create($row);
                $results['inserted']++;
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        return $results;
    }
}
