<?php

namespace App\Models;

use App\Core\Database;

class Enrollment
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM enrollments WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function existsForYear(int $studentId, string $academicYear): bool
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM enrollments WHERE student_id = ? AND academic_year = ? AND status = 'Enrolled'"
        );
        $stmt->execute([$studentId, $academicYear]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO enrollments (school_id, student_id, lop_id, academic_year, enrollment_date, status, created_by, created_at, updated_at)
             VALUES (:school_id, :student_id, :lop_id, :academic_year, CURDATE(), :status, :created_by, NOW(), NOW())'
        );
        $stmt->execute([
            'school_id' => $data['school_id'],
            'student_id' => $data['student_id'],
            'lop_id' => $data['lop_id'],
            'academic_year' => $data['academic_year'],
            'status' => $data['status'] ?? 'Enrolled',
            'created_by' => $data['created_by'],
        ]);
        return (int) $pdo->lastInsertId();
    }
}
