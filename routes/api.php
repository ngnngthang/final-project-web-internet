<?php

use App\Controllers\AuthController;
use App\Controllers\EnrollmentController;
use App\Controllers\GradeController;
use App\Controllers\KhoiController;
use App\Controllers\LopController;
use App\Controllers\ScheduleController;
use App\Controllers\StudentController;
use App\Controllers\TeacherController;
use App\Core\Router;

/** @var Router $router */

// Auth
$router->post('/auth/login', [AuthController::class, 'login']);
$router->post('/auth/logout', [AuthController::class, 'logout']);
$router->get('/auth/me', [AuthController::class, 'me']);

// Khoi
$router->get('/khoi', [KhoiController::class, 'index']);
$router->get('/khoi/{id}', [KhoiController::class, 'show']);
$router->post('/khoi', [KhoiController::class, 'store']);

// Lop
$router->get('/khoi/{khoiId}/lop', [LopController::class, 'index']);
$router->get('/lop/{id}', [LopController::class, 'show']);
$router->post('/khoi/{khoiId}/lop', [LopController::class, 'store']);

// Students / Teachers
$router->get('/students/{id}', [StudentController::class, 'show']);
$router->post('/students', [StudentController::class, 'store']);
$router->post('/students/bulk-import', [StudentController::class, 'bulkImport']);
$router->get('/teachers/{id}', [TeacherController::class, 'show']);
$router->post('/teachers', [TeacherController::class, 'store']);

// Enrollment
$router->post('/lop/{lopId}/enrollments', [EnrollmentController::class, 'store']);

// Schedule
$router->get('/lop/{lopId}/schedule', [ScheduleController::class, 'show']);
$router->post('/lop/{lopId}/schedule', [ScheduleController::class, 'store']);

// Grades (CORE)
$router->post('/lop/{lopId}/scores', [GradeController::class, 'store']);
$router->post('/lop/{lopId}/grades/publish', [GradeController::class, 'publish']);
$router->post('/lop/{lopId}/grades/unpublish', [GradeController::class, 'unpublish']);
$router->get('/lop/{lopId}/grades', [GradeController::class, 'index']);
$router->get('/me/grades', [GradeController::class, 'myGrades']);
