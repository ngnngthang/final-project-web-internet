<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\ForbiddenException;
use App\Models\Schedule;

class ScheduleController
{
    public function show(Request $request, string $lopId): Response
    {
        Auth::require();
        $schedule = Schedule::forLop((int) $lopId);
        return Response::json(['success' => true, 'data' => $schedule]);
    }

    public function store(Request $request, string $lopId): Response
    {
        Auth::require();
        if (Auth::role() !== 'Admin') {
            throw new ForbiddenException('Only Admin can set schedules');
        }

        Schedule::upsert([
            'lop_id' => (int) $lopId,
            'school_id' => Auth::user()['school_id'],
            'days_of_week' => $request->input('days_of_week', []),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'location' => $request->input('location'),
        ]);

        return Response::json(['success' => true, 'data' => Schedule::forLop((int) $lopId)]);
    }
}
