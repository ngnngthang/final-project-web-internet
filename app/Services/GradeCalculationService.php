<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\FinalGrade;
use App\Models\Score;
use Illuminate\Support\Facades\DB;

class GradeCalculationService
{
    public function recalculate(int $studentId, int $lopId): FinalGrade
    {
        $total = Score::query()
            ->where('student_id', $studentId)
            ->where('lop_id', $lopId)
            ->join('assessment_types', 'scores.assessment_type_id', '=', 'assessment_types.id')
            ->whereNotNull('numeric_score')
            ->sum(DB::raw('numeric_score * weight / 100'));

        $enrollment = Enrollment::where('student_id', $studentId)
            ->where('lop_id', $lopId)
            ->firstOrFail();

        return FinalGrade::updateOrCreate(
            ['student_id' => $studentId, 'lop_id' => $lopId, 'academic_year' => $enrollment->academic_year],
            [
                'school_id' => $enrollment->school_id,
                'final_score' => $total,
                'pass_status' => $total >= 5.0 ? 'Pass' : 'Fail',
            ]
        );
    }
}
