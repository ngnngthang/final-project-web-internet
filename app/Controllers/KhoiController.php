<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\ForbiddenException;
use App\Models\Khoi;

class KhoiController
{
    public function index(Request $request): Response
    {
        Auth::require();
        $schoolId = Auth::user()['school_id'];
        return Response::json(['success' => true, 'data' => Khoi::forSchool($schoolId)]);
    }

    public function show(Request $request, string $id): Response
    {
        Auth::require();
        $khoi = Khoi::find((int) $id);
        if (!$khoi) {
            return Response::json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Khoi not found']], 404);
        }
        return Response::json(['success' => true, 'data' => $khoi]);
    }

    public function store(Request $request): Response
    {
        Auth::require();
        if (Auth::role() !== 'Admin') {
            throw new ForbiddenException('Only Admin can create Khoi');
        }

        $id = Khoi::create([
            'school_id' => Auth::user()['school_id'],
            'name' => $request->input('name'),
            'academic_year' => $request->input('academic_year'),
            'created_by' => Auth::id(),
        ]);

        return Response::json(['success' => true, 'data' => Khoi::find($id)], 201);
    }
}
