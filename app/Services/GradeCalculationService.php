<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Enrollment;
use App\Models\FinalGrade;

class GradeCalculationService
{
    /**
     * Recalculates a student's weighted final grade for a Lop and upserts
     * final_grades. Called explicitly by GradeController after every score
     * write (replaces the Observer/DB-trigger approach — plain PHP has
     * neither, so the Controller orchestrates this directly).
     */
    public function recalculate(int $studentId, int $lopId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT SUM(s.numeric_score * at.weight / 100) AS total
             FROM scores s
             JOIN assessment_types at ON s.assessment_type_id = at.id
             WHERE s.student_id = ? AND s.lop_id = ? AND s.numeric_score IS NOT NULL'
        );
        $stmt->execute([$studentId, $lopId]);
        $total = (float) ($stmt->fetchColumn() ?? 0);

        $enrollmentStmt = Database::connection()->prepare(
            'SELECT * FROM enrollments WHERE student_id = ? AND lop_id = ? LIMIT 1'
        );
        $enrollmentStmt->execute([$studentId, $lopId]);
        $enrollment = $enrollmentStmt->fetch();

        if (!$enrollment) {
            throw new \App\Exceptions\BusinessRuleException('Student is not enrolled in this Lop', 'NOT_ENROLLED');
        }

        FinalGrade::upsert([
            'student_id' => $studentId,
            'lop_id' => $lopId,
            'school_id' => $enrollment['school_id'],
            'academic_year' => $enrollment['academic_year'],
            'final_score' => $total,
            'pass_status' => $total >= 5.0 ? 'Pass' : 'Fail',
        ]);

        return FinalGrade::forStudentInLop($studentId, $lopId, $enrollment['academic_year']);
    }
}
