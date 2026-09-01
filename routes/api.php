<?php

use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\KhoiController;
use App\Http\Controllers\Api\LopController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\TeacherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ClassHub API Routes
|--------------------------------------------------------------------------
| Requires laravel/sanctum (composer require laravel/sanctum) for the
| auth:sanctum middleware below. See classhub_php_architecture.md Section 4.
| Controllers are currently stubs — see TODOs inside each file.
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('khoi', KhoiController::class);
    Route::apiResource('lop', LopController::class);
    Route::apiResource('students', StudentController::class);
    Route::post('students/bulk-import', [StudentController::class, 'bulkImport']);
    Route::apiResource('teachers', TeacherController::class);

    Route::apiResource('lop.enrollments', EnrollmentController::class)->shallow();

    Route::apiResource('lop.schedule', ScheduleController::class)->shallow();

    // Grade Management — CORE module
    Route::post('lop/{lop}/scores', [GradeController::class, 'store']);
    Route::put('scores/{score}', [GradeController::class, 'update']);
    Route::post('lop/{lop}/grades/publish', [GradeController::class, 'publish']);
    Route::post('lop/{lop}/grades/unpublish', [GradeController::class, 'unpublish']);
    Route::get('lop/{lop}/grades', [GradeController::class, 'index']);
    Route::get('me/grades', [GradeController::class, 'myGrades']);

    Route::get('reports/grade-summary', [ReportController::class, 'gradeSummary']);
    Route::post('lop/{lop}/reports/export', [ReportController::class, 'export']);
});
