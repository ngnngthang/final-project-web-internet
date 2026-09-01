<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\ForbiddenException;
use App\Models\FinalGrade;
use App\Models\Lop;
use App\Models\Score;
use App\Models\Student;
use App\Policies\GradePolicy;
use App\Services\GradeCalculationService;

class GradeController
{
    /** POST /lop/{lopId}/scores — enter/correct a score, then recalculate the final grade. */
    public function store(Request $request, string $lopId): Response
    {
        Auth::require();
        $lop = Lop::find((int) $lopId);
        if (!$lop) {
            return Response::json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Lop not found']], 404);
        }
        if (!GradePolicy::enter($lop)) {
            throw new ForbiddenException('Not permitted to enter grades for this Lop');
        }

        $numericScore = $request->input('numeric_score');
        if ($numericScore !== null && ($numericScore < 0 || $numericScore > 10)) {
            return Response::json(['success' => false, 'error' => ['code' => 'INVALID_SCORE', 'message' => 'Score must be between 0 and 10']], 400);
        }

        $scoreId = Score::upsert([
            'student_id' => (int) $request->input('student_id'),
            'lop_id' => (int) $lopId,
            'assessment_type_id' => (int) $request->input('assessment_type_id'),
            'teacher_id' => (int) $request->input('teacher_id'),
            'school_id' => $lop['school_id'],
            'numeric_score' => $numericScore,
            'notes' => $request->input('notes'),
            'created_by' => Auth::id(),
        ]);

        $finalGrade = (new GradeCalculationService())->recalculate(
            (int) $request->input('student_id'),
            (int) $lopId
        );

        app_audit_log('entered_score', 'Score', $scoreId, $lop['school_id'], Auth::id(), [
            'numeric_score' => $numericScore,
        ]);

        return Response::json([
            'success' => true,
            'data' => ['score_id' => $scoreId, 'final_grade' => $finalGrade],
        ], 201);
    }

    /** POST /lop/{lopId}/grades/publish */
    public function publish(Request $request, string $lopId): Response
    {
        Auth::require();
        $lop = Lop::find((int) $lopId);
        if (!$lop) {
            return Response::json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Lop not found']], 404);
        }
        if (!GradePolicy::publish($lop)) {
            throw new ForbiddenException('Not permitted to publish grades for this Lop');
        }

        Score::markVerified((int) $lopId);
        FinalGrade::publishForLop((int) $lopId, Auth::id());

        app_audit_log('published_grades', 'Lop', (int) $lopId, $lop['school_id'], Auth::id());

        return Response::json(['success' => true, 'data' => ['message' => 'Grades published', 'lop_id' => (int) $lopId]]);
    }

    /** POST /lop/{lopId}/grades/unpublish */
    public function unpublish(Request $request, string $lopId): Response
    {
        Auth::require();
        $lop = Lop::find((int) $lopId);
        if (!$lop || !GradePolicy::publish($lop)) {
            throw new ForbiddenException('Not permitted to unpublish grades for this Lop');
        }

        FinalGrade::unpublishForLop((int) $lopId);
        app_audit_log('unpublished_grades', 'Lop', (int) $lopId, $lop['school_id'], Auth::id());

        return Response::json(['success' => true, 'data' => ['message' => 'Grades unpublished']]);
    }

    /** GET /lop/{lopId}/grades — Admin/owning Teacher view of all grades in a class. */
    public function index(Request $request, string $lopId): Response
    {
        Auth::require();
        $lop = Lop::find((int) $lopId);
        if (!$lop) {
            return Response::json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Lop not found']], 404);
        }
        if (Auth::role() !== 'Admin' && !\App\Policies\LopPolicy::ownsLop($lop)) {
            throw new ForbiddenException('Not permitted to view grades for this Lop');
        }

        return Response::json(['success' => true, 'data' => FinalGrade::forLop((int) $lopId)]);
    }

    /** GET /me/grades — Student's own published grades only. */
    public function myGrades(Request $request): Response
    {
        Auth::require();
        if (Auth::role() !== 'Student') {
            throw new ForbiddenException('Only students can view their own grades here');
        }

        $student = Student::findByUserId(Auth::id());
        if (!$student) {
            return Response::json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Student profile not found']], 404);
        }

        return Response::json(['success' => true, 'data' => FinalGrade::forStudent($student['id'])]);
    }
}
