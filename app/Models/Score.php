<?php

namespace App\Models;

use App\Core\Database;

class Score
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM scores WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function forStudentInLop(int $studentId, int $lopId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM scores WHERE student_id = ? AND lop_id = ?');
        $stmt->execute([$studentId, $lopId]);
        return $stmt->fetchAll();
    }

    /**
     * Insert or update a score (upsert on the unique key), returns the row id.
     * Mirrors GradeController::store() flow: Controller calls this, then
     * GradeCalculationService::recalculate() — no DB trigger/observer needed,
     * the Controller just calls both explicitly.
     */
    public static function upsert(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO scores (student_id, lop_id, assessment_type_id, teacher_id, school_id, numeric_score, notes, is_verified, created_by, created_at, updated_at)
             VALUES (:student_id, :lop_id, :assessment_type_id, :teacher_id, :school_id, :numeric_score, :notes, 0, :created_by, NOW(), NOW())
             ON DUPLICATE KEY UPDATE numeric_score = VALUES(numeric_score), notes = VALUES(notes), updated_at = NOW()'
        );
        $stmt->execute([
            'student_id' => $data['student_id'],
            'lop_id' => $data['lop_id'],
            'assessment_type_id' => $data['assessment_type_id'],
            'teacher_id' => $data['teacher_id'],
            'school_id' => $data['school_id'],
            'numeric_score' => $data['numeric_score'],
            'notes' => $data['notes'] ?? null,
            'created_by' => $data['created_by'],
        ]);

        $existing = $pdo->prepare(
            'SELECT id FROM scores WHERE student_id = ? AND lop_id = ? AND assessment_type_id = ?'
        );
        $existing->execute([$data['student_id'], $data['lop_id'], $data['assessment_type_id']]);
        return (int) $existing->fetchColumn();
    }

    public static function markVerified(int $lopId): void
    {
        $stmt = Database::connection()->prepare('UPDATE scores SET is_verified = 1 WHERE lop_id = ?');
        $stmt->execute([$lopId]);
    }
}
