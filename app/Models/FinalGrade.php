<?php

namespace App\Models;

use App\Core\Database;

class FinalGrade
{
    public static function forStudentInLop(int $studentId, int $lopId, string $academicYear): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM final_grades WHERE student_id = ? AND lop_id = ? AND academic_year = ?'
        );
        $stmt->execute([$studentId, $lopId, $academicYear]);
        return $stmt->fetch() ?: null;
    }

    public static function forStudent(int $studentId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM final_grades WHERE student_id = ? AND published_at IS NOT NULL'
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public static function forLop(int $lopId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM final_grades WHERE lop_id = ?');
        $stmt->execute([$lopId]);
        return $stmt->fetchAll();
    }

    public static function upsert(array $data): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO final_grades (student_id, lop_id, school_id, academic_year, final_score, pass_status, created_at, updated_at)
             VALUES (:student_id, :lop_id, :school_id, :academic_year, :final_score, :pass_status, NOW(), NOW())
             ON DUPLICATE KEY UPDATE final_score = VALUES(final_score), pass_status = VALUES(pass_status), updated_at = NOW()'
        );
        $stmt->execute([
            'student_id' => $data['student_id'],
            'lop_id' => $data['lop_id'],
            'school_id' => $data['school_id'],
            'academic_year' => $data['academic_year'],
            'final_score' => $data['final_score'],
            'pass_status' => $data['pass_status'],
        ]);
    }

    public static function publishForLop(int $lopId, int $publishedByUserId): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE final_grades SET published_at = NOW(), published_by = ? WHERE lop_id = ?'
        );
        $stmt->execute([$publishedByUserId, $lopId]);
    }

    public static function unpublishForLop(int $lopId): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE final_grades SET published_at = NULL, published_by = NULL WHERE lop_id = ?'
        );
        $stmt->execute([$lopId]);
    }
}
