<?php

namespace App\Services;

use App\Core\Database;
use App\Exceptions\BusinessRuleException;
use App\Models\Enrollment;
use App\Models\Lop;
use App\Models\Student;

class EnrollmentValidationService
{
    public function enroll(int $studentId, int $lopId, string $academicYear, int $createdBy): array
    {
        $student = Student::find($studentId);
        if (!$student) {
            throw new BusinessRuleException('Student not found', 'STUDENT_NOT_FOUND');
        }
        if ($student['status'] !== 'Active') {
            throw new BusinessRuleException('Student is not active', 'STUDENT_INACTIVE');
        }

        $lop = Lop::find($lopId);
        if (!$lop) {
            throw new BusinessRuleException('Lop not found', 'LOP_NOT_FOUND');
        }

        if (Enrollment::existsForYear($studentId, $academicYear)) {
            throw new BusinessRuleException('Student already enrolled this year', 'DUPLICATE_ENROLLMENT');
        }

        if ((int) $lop['current_enrollment'] >= (int) $lop['max_capacity']) {
            throw new BusinessRuleException('Lop at capacity', 'CAPACITY_EXCEEDED');
        }

        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $enrollmentId = Enrollment::create([
                'school_id' => $lop['school_id'],
                'student_id' => $studentId,
                'lop_id' => $lopId,
                'academic_year' => $academicYear,
                'created_by' => $createdBy,
            ]);

            Lop::incrementEnrollment($lopId);

            app_audit_log('enrolled_student', 'Enrollment', $enrollmentId, $lop['school_id'], $createdBy);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        return Enrollment::find($enrollmentId);
    }
}
