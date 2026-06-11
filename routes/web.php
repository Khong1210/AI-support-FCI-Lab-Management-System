<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AiProxyController;
use App\Http\Controllers\AiSchedulerController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingRequestController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\SoftwareController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// =========================================================================
// ── AUTHENTICATION & SESSION MANAGEMENT ──
// =========================================================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


// =========================================================================
// ── AUTHENTICATED PANEL ROUTING (GLOBAL ENCLOSURE) ──
// =========================================================================
Route::middleware(['auth'])->group(function () {

    // Common Landing Dashboards accessible by all logged-in accounts
    Route::get('/admin', [AdminController::class, 'dashboard']);
    Route::get('/admin/mail', [MailController::class, 'index']);


    // ── GROUP A: ADMINISTRATIVE PRIVACY & META CONFIGURATIONS (Strictly Role 1) ──
    Route::middleware(['role:1'])->group(function () {
        // User Account Management Backend
        Route::get('/admin/users', [AdminController::class, 'users']);
        Route::get('/admin/users/add', [AdminController::class, 'createUser']);
        Route::get('/admin/users/create', [AdminController::class, 'createUser']);
        Route::post('/admin/users', [AdminController::class, 'storeUser']);
        Route::get('/admin/users/{user}/edit', [AdminController::class, 'editUser']);
        Route::put('/admin/users/{user}', [AdminController::class, 'updateUser']);
        Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser']);
        
        // Academic Term / Semester Management
        Route::get('/admin/semesters', [SemesterController::class, 'index']);
        Route::get('/admin/semesters/add', [SemesterController::class, 'create']);
        Route::get('/admin/semesters/create', [SemesterController::class, 'create']);
        Route::post('/admin/semesters', [SemesterController::class, 'store']);
        Route::get('/admin/semesters/{semester}/edit', [SemesterController::class, 'edit']);
        Route::put('/admin/semesters/{semester}', [SemesterController::class, 'update']);
        Route::delete('/admin/semesters/{semester}', [SemesterController::class, 'destroy']);

        // AI Engine Integration Modules
        Route::get('/ai-scheduler', [AiSchedulerController::class, 'index']);
        Route::post('/api/ai/generate', [AiProxyController::class, 'generate']);
    });


    // ── GROUP B: ACADEMIC SCHEDULING & WORKFLOW APPROVALS (Role 1 & Role 2) ──
    // Hardlocks timetable structures and authorization endpoints from standard consumers
    Route::middleware(['role:1,2'])->group(function () {
        // Regular Academic Schedule Control Structures
        Route::get('/admin/schedules/add', [ScheduleController::class, 'create']);
        Route::get('/admin/schedules/create', [ScheduleController::class, 'create']);
        Route::post('/admin/schedules', [ScheduleController::class, 'store']);
        Route::get('/admin/schedules/{schedule}/edit', [ScheduleController::class, 'edit']);
        Route::put('/admin/schedules/{schedule}', [ScheduleController::class, 'update']);
        Route::delete('/admin/schedules/{schedule}', [ScheduleController::class, 'destroy']);

        // Extra Class Booking Workflow Decisions (Manager Veto Power)
        Route::put('/admin/booking-requests/{id}/approve', [BookingRequestController::class, 'approve']);
        Route::put('/admin/booking-requests/{id}/reject', [BookingRequestController::class, 'reject']);
        Route::put('/admin/bookings/{booking}/accept', [BookingController::class, 'accept']);
        Route::put('/admin/bookings/{booking}/reject', [BookingController::class, 'reject']);
    });


    // ── GROUP C: INFRASTRUCTURE & ASSET MANAGEMENT (Role 1, Role 3 & Role 4) ──
    // Grants access to Lab Staff (3) for data rendering. Interactive limits must be masked via Blade UI conditional statements.
    Route::middleware(['role:1,3,4'])->group(function () {
        // Laboratory Premises Layout Configurations
        Route::get('/admin/laboratories', [LaboratoryController::class, 'index']);
        Route::get('/admin/laboratories/add', [LaboratoryController::class, 'create']);
        Route::get('/admin/laboratories/create', [LaboratoryController::class, 'create']);
        Route::post('/admin/laboratories', [LaboratoryController::class, 'store']);
        Route::get('/admin/laboratories/{laboratory}/edit', [LaboratoryController::class, 'edit']);
        Route::put('/admin/laboratories/{laboratory}', [LaboratoryController::class, 'update']);
        Route::delete('/admin/laboratories/{laboratory}', [LaboratoryController::class, 'destroy']);

        // Hardware Fleet & Equipment Diagnostics Management
        Route::get('/admin/equipment', [EquipmentController::class, 'index']);
        Route::get('/admin/equipment/add', [EquipmentController::class, 'create']);
        Route::get('/admin/equipment/create', [EquipmentController::class, 'create']);
        Route::post('/admin/equipment', [EquipmentController::class, 'store']);
        Route::get('/admin/equipment/{equipment}/edit', [EquipmentController::class, 'edit']);
        Route::put('/admin/equipment/{equipment}', [EquipmentController::class, 'update']);
        Route::delete('/admin/equipment/{equipment}', [EquipmentController::class, 'destroy']);

        // Academic Software Profile & Catalog Audits
        Route::get('/admin/software', [SoftwareController::class, 'index']);
        Route::get('/admin/software/add', [SoftwareController::class, 'create']);
        Route::get('/admin/software/create', [SoftwareController::class, 'create']);
        Route::post('/admin/software', [SoftwareController::class, 'store']);
        Route::get('/admin/software/{software}/edit', [SoftwareController::class, 'edit']);
        Route::put('/admin/software/{software}', [SoftwareController::class, 'update']);
        Route::delete('/admin/software/{software}', [SoftwareController::class, 'destroy']);
        
        // Technical Breakdown Management & Issue Resolution Updates
        Route::get('/admin/reports', [ReportController::class, 'index']);
        Route::put('/admin/reports/{report}/progress', [ReportController::class, 'markInProgress']);
        Route::put('/admin/reports/{report}/resolve', [ReportController::class, 'resolve']);
        Route::delete('/admin/reports/{report}', [ReportController::class, 'destroy']);
    });


    // ── GROUP D: BROAD CONSUMPTION & DATA DIRECTORIES (All Roles: 1, 2, 3, 4, 5) ──
    // Includes standard read operations and request form entry portals. Ownership validation (Scenario A) is evaluated dynamically inside Controller routines.
    Route::middleware(['role:1,2,3,4,5'])->group(function () {
        // Course Index Registries
        Route::get('/admin/courses', [CourseController::class, 'index']);
        Route::get('/admin/courses/add', [CourseController::class, 'create']);
        Route::get('/admin/courses/create', [CourseController::class, 'create']);
        Route::post('/admin/courses', [CourseController::class, 'store']);
        Route::get('/admin/courses/{course}/edit', [CourseController::class, 'edit']);
        Route::put('/admin/courses/{course}', [CourseController::class, 'update']);
        Route::delete('/admin/courses/{course}', [CourseController::class, 'destroy']);

        // Master Schedule Viewports & Real-time Availability Queries
        Route::get('/admin/schedules', [ScheduleController::class, 'index']);
        Route::get('/admin/schedules/check-occupied', [ScheduleController::class, 'getOccupiedSlots']);
        Route::get('/admin/schedules/check-occupied-slots', [ScheduleController::class, 'checkOccupiedSlots'])->name('schedules.checkSlots');
        Route::get('/admin/schedules/get-available-time-slots', [ScheduleController::class, 'getAvailableTimeSlots']);

        // Historical Extra Class Placement Entries
        Route::get('/admin/bookings', [BookingController::class, 'index']);
        Route::get('/admin/bookings/add', [BookingController::class, 'create']);
        Route::get('/admin/bookings/create', [BookingController::class, 'create']);
        Route::post('/admin/bookings', [BookingController::class, 'store']);
        Route::delete('/admin/bookings/{booking}', [BookingController::class, 'destroy']);

        // Booking Request Pipeline Entrances (Submission & Availability Cross-checking)
        Route::get('/admin/booking-requests', [BookingRequestController::class, 'index']);
        Route::get('/admin/booking-requests/create', [BookingRequestController::class, 'create']);
        Route::post('/admin/booking-requests', [BookingRequestController::class, 'store']);
        Route::get('/admin/booking-requests/check-availability', [BookingRequestController::class, 'checkAvailability']);
        
        // Open Laboratory Fault Log Ticket Openers
        Route::get('/admin/reports/add', [ReportController::class, 'create']);
        Route::get('/admin/reports/create', [ReportController::class, 'create']);
        Route::post('/admin/reports', [ReportController::class, 'store']);
    });
});