<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Enrollment;
use App\Models\Lop;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class EnrollmentValidationService
{
    public function enroll(Student $student, Lop $lop, string $academicYear): Enrollment
    {
        if ($student->status !== 'Active') {
            throw new BusinessRuleException('Student is not active', 'STUDENT_INACTIVE');
        }

        if (Enrollment::where('student_id', $student->id)
            ->where('academic_year', $academicYear)
            ->where('status', 'Enrolled')
            ->exists()) {
            throw new BusinessRuleException('Student already enrolled this year', 'DUPLICATE_ENROLLMENT');
        }

        if ($lop->current_enrollment >= $lop->max_capacity) {
            throw new BusinessRuleException('Lop at capacity', 'CAPACITY_EXCEEDED');
        }

        return DB::transaction(function () use ($student, $lop, $academicYear) {
            $enrollment = Enrollment::create([
                'student_id' => $student->id,
                'lop_id' => $lop->id,
                'school_id' => $lop->school_id,
                'academic_year' => $academicYear,
                'created_by' => auth()->id(),
            ]);

            $lop->increment('current_enrollment');

            return $enrollment;
        });
    }
}
