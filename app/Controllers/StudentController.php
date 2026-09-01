<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\ForbiddenException;
use App\Models\Student;
use App\Policies\StudentPolicy;

class StudentController
{
    public function show(Request $request, string $id): Response
    {
        Auth::require();
        $student = Student::find((int) $id);
        if (!$student) {
            return Response::json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Student not found']], 404);
        }
        return Response::json(['success' => true, 'data' => $student]);
    }

    public function store(Request $request): Response
    {
        Auth::require();
        if (!StudentPolicy::create()) {
            throw new ForbiddenException('Only Admin/Staff can create students');
        }

        $id = Student::create([
            'school_id' => Auth::user()['school_id'],
            'user_id' => $request->input('user_id'),
            'full_name' => $request->input('full_name'),
            'student_id' => $request->input('student_id'),
            'date_of_birth' => $request->input('date_of_birth'),
            'email' => $request->input('email'),
        ]);

        return Response::json(['success' => true, 'data' => Student::find($id)], 201);
    }

    public function bulkImport(Request $request): Response
    {
        Auth::require();
        if (!StudentPolicy::bulkImport()) {
            throw new ForbiddenException('Only Admin/Staff can bulk import students');
        }

        // TODO: wire to App\Services\BulkImportService with an uploaded CSV file.
        return Response::json(['success' => false, 'error' => ['code' => 'NOT_IMPLEMENTED', 'message' => 'Bulk import endpoint not yet wired up']], 501);
    }
}
