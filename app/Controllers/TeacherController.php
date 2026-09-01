<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\ForbiddenException;
use App\Models\Teacher;

class TeacherController
{
    public function show(Request $request, string $id): Response
    {
        Auth::require();
        $teacher = Teacher::find((int) $id);
        if (!$teacher) {
            return Response::json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Teacher not found']], 404);
        }
        return Response::json(['success' => true, 'data' => $teacher]);
    }

    public function store(Request $request): Response
    {
        Auth::require();
        if (Auth::role() !== 'Admin') {
            throw new ForbiddenException('Only Admin can create teachers');
        }

        $id = Teacher::create([
            'school_id' => Auth::user()['school_id'],
            'user_id' => $request->input('user_id'),
            'full_name' => $request->input('full_name'),
            'employee_id' => $request->input('employee_id'),
            'subject' => $request->input('subject'),
        ]);

        return Response::json(['success' => true, 'data' => Teacher::find($id)], 201);
    }
}
