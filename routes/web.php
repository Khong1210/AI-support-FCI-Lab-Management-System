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
    Route::get('/management/mail', [MailController::class, 'index']);


    // ── MASTER PRIVILEGE ISOLATION (Strictly Role 1 - Supreme Authority Only) ──
    Route::middleware(['role:1'])->group(function () {
        // Absolute Destructive Action Restrictions (No Managers allowed to Delete users or Labs)
        Route::delete('/management/users/{user}', [AdminController::class, 'destroyUser']);
        Route::post('/laboratories', [LaboratoryController::class, 'store']);
        Route::delete('/laboratories/{laboratory}', [LaboratoryController::class, 'destroy']);
    });


    // ── HIGH LEVEL MANAGEMENT ROUTING CONTROL (Role 1 & Role 2) ──
    Route::middleware(['role:1,2'])->group(function () {
        // User Account Management Interfaces (CRU for Manager, Admin handles Delete above)
        Route::get('/management/users', [AdminController::class, 'users']);
        Route::get('/management/users/add', [AdminController::class, 'createUser']);
        Route::get('/management/users/create', [AdminController::class, 'createUser']);
        Route::post('/management/users', [AdminController::class, 'storeUser']);
        Route::get('/management/users/{user}/edit', [AdminController::class, 'editUser']);
        Route::put('/management/users/{user}', [AdminController::class, 'updateUser']);

        // Course Index Configuration Catalogs (CRUD for Role 1/2, Read-only for 3/4/5 managed via Blade)
        Route::get('/management/courses/add', [CourseController::class, 'create']);
        Route::get('/management/courses/create', [CourseController::class, 'create']);
        Route::post('/management/courses', [CourseController::class, 'store']);
        Route::get('/management/courses/{course}/edit', [CourseController::class, 'edit']);
        Route::put('/management/courses/{course}', [CourseController::class, 'update']);
        Route::delete('/management/courses/{course}', [CourseController::class, 'destroy']);

        // Academic Term / Semester Management (CRUD)
        Route::get('/management/semesters/add', [SemesterController::class, 'create']);
        Route::get('/management/semesters/create', [SemesterController::class, 'create']);
        Route::post('/management/semesters', [SemesterController::class, 'store']);
        Route::get('/management/semesters/{semester}/edit', [SemesterController::class, 'edit']);
        Route::put('/management/semesters/{semester}', [SemesterController::class, 'update']);
        Route::delete('/management/semesters/{semester}', [SemesterController::class, 'destroy']);

        // AI Engine Integration Modules (CR)
        Route::get('/ai-scheduler', [AiSchedulerController::class, 'index']);
        Route::post('/api/ai/generate', [AiProxyController::class, 'generate']);

        // Core Academic Schedule Structures (CRUD)
        Route::get('/schedules/add', [ScheduleController::class, 'create']);
        Route::get('/schedules/create', [ScheduleController::class, 'create']);
        Route::post('/schedules', [ScheduleController::class, 'store']);
        Route::get('/schedules/{schedule}/edit', [ScheduleController::class, 'edit']);
        Route::put('/schedules/{schedule}', [ScheduleController::class, 'update']);
        Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy']);

        // Workflow Decision Operations (U)
        Route::put('/booking-requests/{id}/approve', [BookingRequestController::class, 'approve']);
        Route::put('/booking-requests/{id}/reject', [BookingRequestController::class, 'reject']);
        Route::put('/bookings/{booking}/accept', [BookingController::class, 'accept']);
        Route::put('/bookings/{booking}/reject', [BookingController::class, 'reject']);

        // Laboratory Management Structural Base Modifications (U for Manager, Admin creates/deletes above)
        Route::get('/laboratories/add', [LaboratoryController::class, 'create']);
        Route::get('/laboratories/create', [LaboratoryController::class, 'create']);
        Route::get('/laboratories/{laboratory}/edit', [LaboratoryController::class, 'edit']);
        Route::put('/laboratories/{laboratory}', [LaboratoryController::class, 'update']);
    });


    // ── FULL PRIVILEGE INFRASTRUCTURE CONTEXT (Role 1, Role 2 & Role 4 - CRUD Hub) ──
    Route::middleware(['role:1,2,4'])->group(function () {
        // Equipment Configuration Control Entries
        Route::post('/equipment', [EquipmentController::class, 'store']);
        Route::delete('/equipment/{equipment}', [EquipmentController::class, 'destroy']);

        // Software Architecture Inventory Entries
        Route::post('/software', [SoftwareController::class, 'store']);
        Route::delete('/software/{software}', [SoftwareController::class, 'destroy']);
    });


    // ── COMBINED LAB INFRASTRUCTURE RESOLUTION LAYER (Role 1, Role 3 & Role 4 - CRUD Hub) ──
    Route::middleware(['role:1,3,4'])->group(function () {
        // Technical Fault Escalation Tracking System Actions
        Route::delete('/reports/{report}', [ReportController::class, 'destroy']);
    });


    // ── SHARED OPERATIONS & LOGISTICS ACCESS BOUNDARY (Role 1, Role 2, Role 3 & Role 4) ──
    Route::middleware(['role:1,2,3,4'])->group(function () {
        // Hardware and Software Creation and Update Gateways
        Route::get('/equipment/add', [EquipmentController::class, 'create']);
        Route::get('/equipment/create', [EquipmentController::class, 'create']);
        Route::get('/equipment/{equipment}/edit', [EquipmentController::class, 'edit']);
        Route::put('/equipment/{equipment}', [EquipmentController::class, 'update']);

        Route::get('/software/add', [SoftwareController::class, 'create']);
        Route::get('/software/create', [SoftwareController::class, 'create']);
        Route::get('/software/{software}/edit', [SoftwareController::class, 'edit']);
        Route::put('/software/{software}', [SoftwareController::class, 'update']);

        // Diagnostics Resolution Operations
        Route::put('/reports/{report}/progress', [ReportController::class, 'markInProgress']);
        Route::put('/reports/{report}/resolve', [ReportController::class, 'resolve']);
    });


    // ── UNIVERSAL ROOT VIEW INTERFACES LAYER (Role 1, Role 2, Role 3, Role 4 & Role 5) ──
    Route::middleware(['role:1,2,3,4,5'])->group(function () {
        // Course Index Configuration Catalogs (CRUD for Role 1/2, Read-only for 3/4/5 managed via Blade)
        Route::get('/management/courses', [CourseController::class, 'index']);
        Route::get('/management/semesters', [SemesterController::class, 'index']);

        // Universal Read Directories
        Route::get('/laboratories', [LaboratoryController::class, 'index']);
        Route::get('/equipment', [EquipmentController::class, 'index']);
        Route::get('/software', [SoftwareController::class, 'index']);

        // Master Schedule Real-time Layout Viewports
        Route::get('/schedules', [ScheduleController::class, 'index']);
        Route::get('/schedules/check-occupied', [ScheduleController::class, 'getOccupiedSlots']);
        Route::get('/schedules/check-occupied-slots', [ScheduleController::class, 'checkOccupiedSlots'])->name('schedules.checkSlots');
        Route::get('/schedules/get-available-time-slots', [ScheduleController::class, 'getAvailableTimeSlots']);

        // Placement Log Registry Entrances (CRUD for All Roles)
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::get('/bookings/add', [BookingController::class, 'create']);
        Route::get('/bookings/create', [BookingController::class, 'create']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);

        // Booking Request Pipeline System Routes
        Route::get('/booking-requests', [BookingRequestController::class, 'index']);
        Route::get('/booking-requests/create', [BookingRequestController::class, 'create']);
        Route::post('/booking-requests', [BookingRequestController::class, 'store']);
        Route::get('/booking-requests/check-availability', [BookingRequestController::class, 'checkAvailability']);

        // Open Fault Ticket Logging Ports
        Route::get('/reports', [ReportController::class, 'index']);
        Route::get('/reports/add', [ReportController::class, 'create']);
        Route::get('/reports/create', [ReportController::class, 'create']);
        Route::post('/reports', [ReportController::class, 'store']);
    });
});