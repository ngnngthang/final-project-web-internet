<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\ForbiddenException;
use App\Models\Lop;
use App\Policies\LopPolicy;

class LopController
{
    public function index(Request $request, string $khoiId): Response
    {
        Auth::require();
        return Response::json(['success' => true, 'data' => Lop::forKhoi((int) $khoiId)]);
    }

    public function show(Request $request, string $id): Response
    {
        Auth::require();
        $lop = Lop::find((int) $id);
        if (!$lop) {
            return Response::json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Lop not found']], 404);
        }
        if (!LopPolicy::view($lop)) {
            throw new ForbiddenException('Not permitted to view this Lop');
        }
        return Response::json(['success' => true, 'data' => $lop]);
    }

    public function store(Request $request, string $khoiId): Response
    {
        Auth::require();
        if (!LopPolicy::create()) {
            throw new ForbiddenException('Only Admin can create Lop');
        }

        $id = Lop::create([
            'khoi_id' => (int) $khoiId,
            'school_id' => Auth::user()['school_id'],
            'name' => $request->input('name'),
            'max_capacity' => $request->input('max_capacity', 50),
        ]);

        return Response::json(['success' => true, 'data' => Lop::find($id)], 201);
    }
}
