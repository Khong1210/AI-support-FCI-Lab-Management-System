<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SoftwareController;
use App\Models\Schedule;
use App\Models\Software;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin', [AdminController::class, 'dashboard']);

// User management
Route::get('/admin/users', [AdminController::class, 'users']);
Route::get('/admin/users/add', [AdminController::class, 'createUser']);
Route::get('/admin/users/create', [AdminController::class, 'createUser']);
Route::post('/admin/users', [AdminController::class, 'storeUser']);
Route::get('/admin/users/{user}/edit', [AdminController::class, 'editUser']);
Route::put('/admin/users/{user}', [AdminController::class, 'updateUser']);
Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser']);

// Course management
use App\Http\Controllers\CourseController;
Route::get('/admin/courses', [CourseController::class, 'index']);
Route::get('/admin/courses/add', [CourseController::class, 'create']);
Route::get('/admin/courses/create', [CourseController::class, 'create']);
Route::post('/admin/courses', [CourseController::class, 'store']);
Route::get('/admin/courses/{course}/edit', [CourseController::class, 'edit']);
Route::put('/admin/courses/{course}', [CourseController::class, 'update']);
Route::delete('/admin/courses/{course}', [CourseController::class, 'destroy']);

// Laboratory management
Route::get('/admin/laboratories', [LaboratoryController::class, 'index']);
Route::get('/admin/laboratories/add', [LaboratoryController::class, 'create']);
Route::get('/admin/laboratories/create', [LaboratoryController::class, 'create']);
Route::post('/admin/laboratories', [LaboratoryController::class, 'store']);
Route::get('/admin/laboratories/{laboratory}/edit', [LaboratoryController::class, 'edit']);
Route::put('/admin/laboratories/{laboratory}', [LaboratoryController::class, 'update']);
Route::delete('/admin/laboratories/{laboratory}', [LaboratoryController::class, 'destroy']);

// Equipment management
Route::get('/admin/equipment', [EquipmentController::class, 'index']);
Route::get('/admin/equipment/add', [EquipmentController::class, 'create']);
Route::get('/admin/equipment/create', [EquipmentController::class, 'create']);
Route::post('/admin/equipment', [EquipmentController::class, 'store']);
Route::get('/admin/equipment/{equipment}/edit', [EquipmentController::class, 'edit']);
Route::put('/admin/equipment/{equipment}', [EquipmentController::class, 'update']);
Route::delete('/admin/equipment/{equipment}', [EquipmentController::class, 'destroy']);

// Software management
Route::get('/admin/software', [SoftwareController::class, 'index']);
Route::get('/admin/software/add', [SoftwareController::class, 'create']);
Route::get('/admin/software/create', [SoftwareController::class, 'create']);
Route::post('/admin/software', [SoftwareController::class, 'store']);
Route::get('/admin/software/{software}/edit', [SoftwareController::class, 'edit']);
Route::put('/admin/software/{software}', [SoftwareController::class, 'update']);
Route::delete('/admin/software/{software}', [SoftwareController::class, 'destroy']);

// Booking management
Route::get('/admin/bookings', [BookingController::class, 'index']);
Route::get('/admin/bookings/add', [BookingController::class, 'create']);
Route::get('/admin/bookings/create', [BookingController::class, 'create']);
Route::post('/admin/bookings', [BookingController::class, 'store']);
Route::put('/admin/bookings/{booking}/accept', [BookingController::class, 'accept']);
Route::put('/admin/bookings/{booking}/reject', [BookingController::class, 'reject']);
Route::delete('/admin/bookings/{booking}', [BookingController::class, 'destroy']);

// Schedule management
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SemesterController;
Route::get('/admin/schedules', [ScheduleController::class, 'index']);
Route::get('/admin/schedules/add', [ScheduleController::class, 'create']);
Route::get('/admin/schedules/create', [ScheduleController::class, 'create']);
Route::post('/admin/schedules', [ScheduleController::class, 'store']);
Route::get('/admin/schedules/{schedule}/edit', [ScheduleController::class, 'edit']);
Route::put('/admin/schedules/{schedule}', [ScheduleController::class, 'update']);
Route::delete('/admin/schedules/{schedule}', [ScheduleController::class, 'destroy']);

// Semester management
Route::get('/admin/semesters', [SemesterController::class, 'index']);
Route::get('/admin/semesters/add', [SemesterController::class, 'create']);
Route::get('/admin/semesters/create', [SemesterController::class, 'create']);
Route::post('/admin/semesters', [SemesterController::class, 'store']);
Route::get('/admin/semesters/{semester}/edit', [SemesterController::class, 'edit']);
Route::put('/admin/semesters/{semester}', [SemesterController::class, 'update']);
Route::delete('/admin/semesters/{semester}', [SemesterController::class, 'destroy']);

// Report problem management
Route::get('/admin/reports', [ReportController::class, 'index']);
Route::get('/admin/reports/add', [ReportController::class, 'create']);
Route::get('/admin/reports/create', [ReportController::class, 'create']);
Route::post('/admin/reports', [ReportController::class, 'store']);
Route::put('/admin/reports/{report}/progress', [ReportController::class, 'markInProgress']);
Route::put('/admin/reports/{report}/resolve', [ReportController::class, 'resolve']);
Route::delete('/admin/reports/{report}', [ReportController::class, 'destroy']);

// Mail / inbox
Route::get('/admin/mail', [MailController::class, 'index']);

Route::get('/ai-scheduler', function () {
    $schedules = Schedule::all();
    $software = Software::all();

    return view('ai-scheduler', compact('schedules', 'software'));
});
