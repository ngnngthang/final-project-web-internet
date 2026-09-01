<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\ForbiddenException;
use App\Policies\EnrollmentPolicy;
use App\Services\EnrollmentValidationService;

class EnrollmentController
{
    public function store(Request $request, string $lopId): Response
    {
        Auth::require();
        if (!EnrollmentPolicy::create()) {
            throw new ForbiddenException('Only Admin/Staff can enroll students');
        }

        $service = new EnrollmentValidationService();
        $enrollment = $service->enroll(
            studentId: (int) $request->input('student_id'),
            lopId: (int) $lopId,
            academicYear: $request->input('academic_year'),
            createdBy: Auth::id()
        );

        return Response::json(['success' => true, 'data' => $enrollment], 201);
    }
}
