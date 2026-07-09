@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@php
    $user = auth()->user();
    $role = (int) $user->user_role;
@endphp

@section('content')

{{-- ======================================================================== --}}
{{-- ROLE 1: ADMIN — kept byte-for-byte identical to original                 --}}
{{-- ======================================================================== --}}
@if ($role === 1)

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Users Card -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border-l-4 border-blue-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Total Users</p>
                        <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $counts['users'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-blue-100 rounded-full p-4">
                        <i class="fas fa-users text-blue-600 text-2xl"></i>
                    </div>
                </div>
                <a href="{{ url('/admin/users') }}" class="mt-4 inline-flex items-center text-blue-600 hover:text-blue-700 font-medium text-sm">
                    View Details <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- Equipment Card -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border-l-4 border-green-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Equipment</p>
                        <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $counts['equipment'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-green-100 rounded-full p-4">
                        <i class="fas fa-desktop text-green-600 text-2xl"></i>
                    </div>
                </div>
                <a href="{{ url('/admin/equipment') }}" class="mt-4 inline-flex items-center text-green-600 hover:text-green-700 font-medium text-sm">
                    View Details <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- Software Card -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border-l-4 border-amber-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Software</p>
                        <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $counts['software'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-amber-100 rounded-full p-4">
                        <i class="fas fa-cube text-amber-600 text-2xl"></i>
                    </div>
                </div>
                <a href="{{ url('/admin/software') }}" class="mt-4 inline-flex items-center text-amber-600 hover:text-amber-700 font-medium text-sm">
                    View Details <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- Bookings Card -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border-l-4 border-red-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Bookings</p>
                        <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $counts['bookings'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-red-100 rounded-full p-4">
                        <i class="fas fa-calendar-alt text-red-600 text-2xl"></i>
                    </div>
                </div>
                <a href="{{ url('/admin/bookings') }}" class="mt-4 inline-flex items-center text-red-600 hover:text-red-700 font-medium text-sm">
                    View Details <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- System Overview Card -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center mb-6">
                <i class="fas fa-chart-line text-blue-600 text-xl mr-3"></i>
                <h3 class="text-xl font-bold text-slate-900">System Overview</h3>
            </div>
            
            <p class="text-slate-600 mb-6">
                Welcome to the laboratory management system admin panel. Here you can manage all aspects of the lab system including equipment, software, courses, and bookings. Use the sidebar to navigate between modules.
            </p>

            <!-- Overview Stats -->
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-flask-vial text-blue-600 text-2xl mb-2"></i>
                        <p class="text-2xl font-bold text-blue-900">{{ $counts['laboratories'] ?? 0 }}</p>
                        <p class="text-xs text-blue-700 font-medium mt-1">LABORATORIES</p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-book text-purple-600 text-2xl mb-2"></i>
                        <p class="text-2xl font-bold text-purple-900">{{ $counts['courses'] ?? 0 }}</p>
                        <p class="text-xs text-purple-700 font-medium mt-1">COURSES</p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-file-alt text-orange-600 text-2xl mb-2"></i>
                        <p class="text-2xl font-bold text-orange-900">{{ $counts['reports'] ?? 0 }}</p>
                        <p class="text-xs text-orange-700 font-medium mt-1">REPORTS</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg shadow-md p-6 text-white">
            <div class="flex items-center mb-6">
                <i class="fas fa-lightning-bolt text-yellow-200 text-xl mr-3"></i>
                <h3 class="text-xl font-bold">Quick Actions</h3>
            </div>

            <div class="space-y-3">
                <a href="{{ url('/admin/users/add') }}" class="block bg-white/20 hover:bg-white/30 transition rounded-lg p-4 border border-white/30 backdrop-blur-sm">
                    <div class="flex items-center">
                        <div class="bg-white/30 rounded-lg p-3 mr-3">
                            <i class="fas fa-user-plus text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Create New User</p>
                            <p class="text-sm opacity-90">Add a new user to the system</p>
                        </div>
                    </div>
                </a>

                <a href="{{ url('/admin/equipment') }}" class="block bg-white/20 hover:bg-white/30 transition rounded-lg p-4 border border-white/30 backdrop-blur-sm">
                    <div class="flex items-center">
                        <div class="bg-white/30 rounded-lg p-3 mr-3">
                            <i class="fas fa-server text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Review Inventory</p>
                            <p class="text-sm opacity-90">Check equipment status</p>
                        </div>
                    </div>
                </a>

                <a href="{{ url('/admin/semesters') }}" class="block bg-white/20 hover:bg-white/30 transition rounded-lg p-4 border border-white/30 backdrop-blur-sm">
                    <div class="flex items-center">
                        <div class="bg-white/30 rounded-lg p-3 mr-3">
                            <i class="fas fa-calendar text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Manage Semesters</p>
                            <p class="text-sm opacity-90">Update academic schedule</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- System Status Section -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-slate-900 flex items-center">
                <i class="fas fa-history text-blue-600 mr-3"></i>
                System Status
            </h3>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                <span class="w-2 h-2 bg-green-600 rounded-full mr-2"></span>
                All Systems Operational
            </span>
        </div>

        <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                    <div>
                        <p class="font-medium text-slate-900">Database</p>
                        <p class="text-sm text-slate-600">Connected and operational</p>
                    </div>
                </div>
                <span class="text-green-600 font-semibold text-sm">Active</span>
            </div>

            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                    <div>
                        <p class="font-medium text-slate-900">User Authentication</p>
                        <p class="text-sm text-slate-600">All authentication services running</p>
                    </div>
                </div>
                <span class="text-green-600 font-semibold text-sm">Active</span>
            </div>

            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                    <div>
                        <p class="font-medium text-slate-900">File Storage</p>
                        <p class="text-sm text-slate-600">Storage system operational</p>
                    </div>
                </div>
                <span class="text-green-600 font-semibold text-sm">Active</span>
            </div>
        </div>
    </div>

{{-- ======================================================================== --}}
{{-- ROLE 2: FACULTY MANAGER — Approvals + Academic Oversight                 --}}
@elseif ($role === 2)

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Pending Booking Requests -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border-l-4 border-amber-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Pending Booking Requests</p>
                        <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $counts['pending_booking_requests'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-amber-100 rounded-full p-4">
                        <i class="fas fa-clock text-amber-600 text-2xl"></i>
                    </div>
                </div>
                <a href="{{ url('/booking-requests') }}" class="mt-4 inline-flex items-center text-amber-600 hover:text-amber-700 font-medium text-sm">
                    Review Requests <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- Pending Software Requests -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border-l-4 border-orange-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Pending Software Requests</p>
                        <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $counts['pending_software_requests'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-orange-100 rounded-full p-4">
                        <i class="fas fa-cube text-orange-600 text-2xl"></i>
                    </div>
                </div>
                <a href="{{ url('/software-requests') }}" class="mt-4 inline-flex items-center text-orange-600 hover:text-orange-700 font-medium text-sm">
                    Review Requests <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- Active Courses -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border-l-4 border-purple-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Active Courses</p>
                        <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $counts['active_courses'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-purple-100 rounded-full p-4">
                        <i class="fas fa-book text-purple-600 text-2xl"></i>
                    </div>
                </div>
                <a href="{{ url('/management/courses') }}" class="mt-4 inline-flex items-center text-purple-600 hover:text-purple-700 font-medium text-sm">
                    View Courses <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- Active Schedules -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border-l-4 border-teal-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Active Schedules</p>
                        <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $counts['active_schedules'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-teal-100 rounded-full p-4">
                        <i class="fas fa-calendar-week text-teal-600 text-2xl"></i>
                    </div>
                </div>
                <a href="{{ url('/schedules') }}" class="mt-4 inline-flex items-center text-teal-600 hover:text-teal-700 font-medium text-sm">
                    View Schedules <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Approval Queue Summary -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center mb-6">
                <i class="fas fa-clipboard-check text-amber-600 text-xl mr-3"></i>
                <h3 class="text-xl font-bold text-slate-900">Approval Queue Summary</h3>
            </div>

            <p class="text-slate-600 mb-4">
                Review and approve pending booking and software requests. Approved bookings are automatically scheduled.
            </p>

            <!-- Pending Booking Requests Preview -->
            <div class="mb-6">
                <h4 class="font-semibold text-slate-700 mb-3">
                    <i class="fas fa-calendar-alt mr-2 text-amber-600"></i>Pending Booking Requests
                </h4>
                @if (!empty($pendingBookings) && count($pendingBookings))
                    <div class="space-y-2">
                        @foreach ($pendingBookings as $req)
                            <div class="flex items-center justify-between bg-amber-50 rounded-lg p-3 border border-amber-200">
                                <div>
                                    <span class="font-medium text-slate-800">{{ $req->user->username ?? 'User #' . $req->user_id }}</span>
                                    <span class="text-slate-500 text-sm ml-2">— {{ $req->laboratory->lab_name ?? 'Lab #' . $req->lab_id }}</span>
                                    <br>
                                    <span class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($req->date)->format('d M Y') }} | {{ substr($req->start_time, 0, 5) }} – {{ substr($req->end_time, 0, 5) }}</span>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Pending</span>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ url('/booking-requests') }}" class="inline-flex items-center text-sm text-amber-600 hover:text-amber-700 mt-3 font-medium">
                        View All Booking Requests <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                @else
                    <p class="text-slate-400 text-sm italic">No pending booking requests.</p>
                @endif
            </div>

            <!-- Pending Software Requests Preview -->
            <div>
                <h4 class="font-semibold text-slate-700 mb-3">
                    <i class="fas fa-cube mr-2 text-orange-600"></i>Pending Software Requests
                </h4>
                @if (!empty($pendingSoftware) && count($pendingSoftware))
                    <div class="space-y-2">
                        @foreach ($pendingSoftware as $req)
                            <div class="flex items-center justify-between bg-orange-50 rounded-lg p-3 border border-orange-200">
                                <div>
                                    <span class="font-medium text-slate-800">{{ $req->user->username ?? 'User #' . $req->user_id }}</span>
                                    <span class="text-slate-500 text-sm ml-2">— {{ $req->software->name ?? 'Software #' . $req->software_id }}</span>
                                    @if ($req->version)
                                        <span class="text-xs text-slate-400 ml-1">v{{ $req->version }}</span>
                                    @endif
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Pending</span>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ url('/software-requests') }}" class="inline-flex items-center text-sm text-orange-600 hover:text-orange-700 mt-3 font-medium">
                        View All Software Requests <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                @else
                    <p class="text-slate-400 text-sm italic">No pending software requests.</p>
                @endif
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-lg shadow-md p-6 text-white">
            <div class="flex items-center mb-6">
                <i class="fas fa-lightning-bolt text-yellow-200 text-xl mr-3"></i>
                <h3 class="text-xl font-bold">Quick Actions</h3>
            </div>

            <div class="space-y-3">
                <a href="{{ url('/ai-scheduler') }}" class="block bg-white/20 hover:bg-white/30 transition rounded-lg p-4 border border-white/30 backdrop-blur-sm">
                    <div class="flex items-center">
                        <div class="bg-white/30 rounded-lg p-3 mr-3">
                            <i class="fas fa-robot text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="font-semibold">AI Scheduler</p>
                            <p class="text-sm opacity-90">Generate optimized schedules</p>
                        </div>
                    </div>
                </a>

                <a href="{{ url('/equipment') }}" class="block bg-white/20 hover:bg-white/30 transition rounded-lg p-4 border border-white/30 backdrop-blur-sm">
                    <div class="flex items-center">
                        <div class="bg-white/30 rounded-lg p-3 mr-3">
                            <i class="fas fa-server text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Review Inventory</p>
                            <p class="text-sm opacity-90">Check equipment status</p>
                        </div>
                    </div>
                </a>

                <a href="{{ url('/management/semesters') }}" class="block bg-white/20 hover:bg-white/30 transition rounded-lg p-4 border border-white/30 backdrop-blur-sm">
                    <div class="flex items-center">
                        <div class="bg-white/30 rounded-lg p-3 mr-3">
                            <i class="fas fa-calendar text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Manage Semesters</p>
                            <p class="text-sm opacity-90">Update academic schedule</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    </div>

    <!-- System Overview Mini-Cards -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center mb-4">
            <i class="fas fa-chart-pie text-slate-600 text-xl mr-3"></i>
            <h3 class="text-xl font-bold text-slate-900">Additional Overview</h3>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-lg p-4 border border-slate-200">
                <div class="flex flex-col items-center">
                    <i class="fas fa-flask-vial text-slate-600 text-2xl mb-2"></i>
                    <p class="text-2xl font-bold text-slate-900">{{ $counts['laboratories'] ?? 0 }}</p>
                    <p class="text-xs text-slate-700 font-medium mt-1">LABORATORIES</p>
                </div>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                <div class="flex flex-col items-center">
                    <i class="fas fa-desktop text-green-600 text-2xl mb-2"></i>
                    <p class="text-2xl font-bold text-green-900">{{ $counts['equipment'] ?? 0 }}</p>
                    <p class="text-xs text-green-700 font-medium mt-1">EQUIPMENT</p>
                </div>
            </div>
            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg p-4 border border-amber-200">
                <div class="flex flex-col items-center">
                    <i class="fas fa-cube text-amber-600 text-2xl mb-2"></i>
                    <p class="text-2xl font-bold text-amber-900">{{ $counts['software'] ?? 0 }}</p>
                    <p class="text-xs text-amber-700 font-medium mt-1">SOFTWARE</p>
                </div>
            </div>
        </div>
    </div>

{{-- ======================================================================== --}}
{{-- ROLE 3: LAB STAFF — Fault Reports + Inventory                           --}}
{{-- ======================================================================== --}}
@elseif ($role === 3)

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Open Fault Reports -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border-l-4 border-red-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Open Fault Reports</p>
                        <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $counts['open_reports'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-red-100 rounded-full p-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                    </div>
                </div>
                <a href="{{ url('/reports') }}" class="mt-4 inline-flex items-center text-red-600 hover:text-red-700 font-medium text-sm">
                    View Reports <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- Equipment -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border-l-4 border-green-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Equipment</p>
                        <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $counts['equipment'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-green-100 rounded-full p-4">
                        <i class="fas fa-desktop text-green-600 text-2xl"></i>
                    </div>
                </div>
                <a href="{{ url('/equipment') }}" class="mt-4 inline-flex items-center text-green-600 hover:text-green-700 font-medium text-sm">
                    View Details <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- Software -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border-l-4 border-amber-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Software</p>
                        <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $counts['software'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-amber-100 rounded-full p-4">
                        <i class="fas fa-cube text-amber-600 text-2xl"></i>
                    </div>
                </div>
                <a href="{{ url('/software') }}" class="mt-4 inline-flex items-center text-amber-600 hover:text-amber-700 font-medium text-sm">
                    View Details <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- Laboratories -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border-l-4 border-blue-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Laboratories</p>
                        <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $counts['laboratories'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-blue-100 rounded-full p-4">
                        <i class="fas fa-flask-vial text-blue-600 text-2xl"></i>
                    </div>
                </div>
                <a href="{{ url('/laboratories') }}" class="mt-4 inline-flex items-center text-blue-600 hover:text-blue-700 font-medium text-sm">
                    View Labs <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Recent Fault Reports -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center mb-6">
                <i class="fas fa-tools text-red-600 text-xl mr-3"></i>
                <h3 class="text-xl font-bold text-slate-900">Recent Fault Reports</h3>
            </div>

            <p class="text-slate-600 mb-4">
                Open fault reports that need your attention. Mark them as In Progress when you start working, and Resolved when fixed.
            </p>

            @if (!empty($openReports) && count($openReports))
                <div class="space-y-3">
                    @foreach ($openReports as $report)
                        @php
                            $reportStatusClass = match ((int) $report->status) {
                                2 => 'bg-amber-100 text-amber-800',
                                3 => 'bg-green-100 text-green-800',
                                default => 'bg-red-100 text-red-800',
                            };
                            $reportStatusLabel = match ((int) $report->status) {
                                2 => 'In Progress',
                                3 => 'Resolved',
                                default => 'Open',
                            };
                        @endphp
                        <div class="flex items-center justify-between bg-red-50 rounded-lg p-4 border border-red-200">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-slate-800">{{ $report->laboratory->lab_name ?? 'Lab #' . $report->lab_id }}</span>
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $reportStatusClass }}">{{ $reportStatusLabel }}</span>
                                </div>
                                <p class="text-sm text-slate-600 mt-1">{{ Str::limit($report->description, 80) }}</p>
                                <p class="text-xs text-slate-400 mt-1">
                                    Reported by {{ $report->user->username ?? 'User #' . $report->user_id }}
                                    — {{ \Carbon\Carbon::parse($report->reported_date)->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <a href="{{ url('/reports') }}" class="inline-flex items-center text-sm text-red-600 hover:text-red-700 mt-4 font-medium">
                    View All Reports <i class="fas fa-arrow-right ml-1"></i>
                </a>
            @else
                <p class="text-slate-400 text-sm italic">No open fault reports — great job!</p>
            @endif
        </div>

        <!-- Quick Actions Card -->
        <div class="bg-gradient-to-br from-red-600 to-pink-700 rounded-lg shadow-md p-6 text-white">
            <div class="flex items-center mb-6">
                <i class="fas fa-lightning-bolt text-yellow-200 text-xl mr-3"></i>
                <h3 class="text-xl font-bold">Quick Actions</h3>
            </div>

            <div class="space-y-3">
                <a href="{{ url('/reports') }}" class="block bg-white/20 hover:bg-white/30 transition rounded-lg p-4 border border-white/30 backdrop-blur-sm">
                    <div class="flex items-center">
                        <div class="bg-white/30 rounded-lg p-3 mr-3">
                            <i class="fas fa-clipboard-list text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="font-semibold">View Fault Reports</p>
                            <p class="text-sm opacity-90">Triage and resolve issues</p>
                        </div>
                    </div>
                </a>

                <a href="{{ url('/equipment') }}" class="block bg-white/20 hover:bg-white/30 transition rounded-lg p-4 border border-white/30 backdrop-blur-sm">
                    <div class="flex items-center">
                        <div class="bg-white/30 rounded-lg p-3 mr-3">
                            <i class="fas fa-server text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Review Inventory</p>
                            <p class="text-sm opacity-90">Check equipment status</p>
                        </div>
                    </div>
                </a>

                <a href="{{ url('/software') }}" class="block bg-white/20 hover:bg-white/30 transition rounded-lg p-4 border border-white/30 backdrop-blur-sm">
                    <div class="flex items-center">
                        <div class="bg-white/30 rounded-lg p-3 mr-3">
                            <i class="fas fa-cube text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Manage Software</p>
                            <p class="text-sm opacity-90">Update software inventory</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

{{-- ======================================================================== --}}
{{-- ROLE 4: LAB COMMITTEE — Oversight of Reports, Equipment, Software, Bookings --}}
{{-- ======================================================================== --}}
@elseif ($role === 4)

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Open Fault Reports -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border-l-4 border-red-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Open Fault Reports</p>
                        <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $counts['open_reports'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-red-100 rounded-full p-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                    </div>
                </div>
                <a href="{{ url('/reports') }}" class="mt-4 inline-flex items-center text-red-600 hover:text-red-700 font-medium text-sm">
                    View Reports <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- Equipment -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border-l-4 border-green-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Equipment</p>
                        <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $counts['equipment'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-green-100 rounded-full p-4">
                        <i class="fas fa-desktop text-green-600 text-2xl"></i>
                    </div>
                </div>
                <a href="{{ url('/equipment') }}" class="mt-4 inline-flex items-center text-green-600 hover:text-green-700 font-medium text-sm">
                    View Details <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- Software -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border-l-4 border-amber-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Software</p>
                        <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $counts['software'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-amber-100 rounded-full p-4">
                        <i class="fas fa-cube text-amber-600 text-2xl"></i>
                    </div>
                </div>
                <a href="{{ url('/software') }}" class="mt-4 inline-flex items-center text-amber-600 hover:text-amber-700 font-medium text-sm">
                    View Details <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- Active Bookings -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border-l-4 border-indigo-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Active Bookings</p>
                        <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $counts['active_bookings'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-indigo-100 rounded-full p-4">
                        <i class="fas fa-calendar-alt text-indigo-600 text-2xl"></i>
                    </div>
                </div>
                <a href="{{ url('/schedules') }}" class="mt-4 inline-flex items-center text-indigo-600 hover:text-indigo-700 font-medium text-sm">
                    View Schedules <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Lab Status Overview -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center mb-6">
                <i class="fas fa-chart-bar text-purple-600 text-xl mr-3"></i>
                <h3 class="text-xl font-bold text-slate-900">Lab Status Overview</h3>
            </div>

            <p class="text-slate-600 mb-6">
                Monitor fault report status and resource inventory across all laboratories. Keep track of open issues and ensure equipment and software are properly maintained.
            </p>

            <!-- Report Status Breakdown -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4 border border-red-200">
                    <div class="flex flex-col items-center">
                        <p class="text-2xl font-bold text-red-900">{{ $counts['open_reports'] ?? 0 }}</p>
                        <p class="text-xs text-red-700 font-medium mt-1">OPEN</p>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg p-4 border border-amber-200">
                    <div class="flex flex-col items-center">
                        <p class="text-2xl font-bold text-amber-900">{{ $counts['in_progress_reports'] ?? 0 }}</p>
                        <p class="text-xs text-amber-700 font-medium mt-1">IN PROGRESS</p>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                    <div class="flex flex-col items-center">
                        <p class="text-2xl font-bold text-green-900">{{ $counts['resolved_reports'] ?? 0 }}</p>
                        <p class="text-xs text-green-700 font-medium mt-1">RESOLVED</p>
                    </div>
                </div>
            </div>

            <!-- Recent Open Reports -->
            <h4 class="font-semibold text-slate-700 mb-3">
                <i class="fas fa-exclamation-circle mr-2 text-red-600"></i>Open Reports Needing Attention
            </h4>
            @if (!empty($openReports) && count($openReports))
                <div class="space-y-2">
                    @foreach ($openReports as $report)
                        <div class="flex items-center justify-between bg-red-50 rounded-lg p-3 border border-red-200">
                            <div>
                                <span class="font-medium text-slate-800">{{ $report->laboratory->lab_name ?? 'Lab #' . $report->lab_id }}</span>
                                <span class="text-slate-500 text-sm ml-2">— {{ Str::limit($report->description, 60) }}</span>
                                <br>
                                <span class="text-xs text-slate-400">
                                    Reported by {{ $report->user->username ?? 'User #' . $report->user_id }}
                                    — {{ \Carbon\Carbon::parse($report->reported_date)->format('d M Y') }}
                                </span>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Open</span>
                        </div>
                    @endforeach
                </div>
                <a href="{{ url('/reports') }}" class="inline-flex items-center text-sm text-red-600 hover:text-red-700 mt-3 font-medium">
                    View All Reports <i class="fas fa-arrow-right ml-1"></i>
                </a>
            @else
                <p class="text-slate-400 text-sm italic">No open reports — all clear.</p>
            @endif
        </div>

        <!-- Quick Actions Card -->
        <div class="bg-gradient-to-br from-purple-600 to-indigo-700 rounded-lg shadow-md p-6 text-white">
            <div class="flex items-center mb-6">
                <i class="fas fa-lightning-bolt text-yellow-200 text-xl mr-3"></i>
                <h3 class="text-xl font-bold">Quick Actions</h3>
            </div>

            <div class="space-y-3">
                <a href="{{ url('/reports') }}" class="block bg-white/20 hover:bg-white/30 transition rounded-lg p-4 border border-white/30 backdrop-blur-sm">
                    <div class="flex items-center">
                        <div class="bg-white/30 rounded-lg p-3 mr-3">
                            <i class="fas fa-clipboard-list text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="font-semibold">View Reports</p>
                            <p class="text-sm opacity-90">Monitor fault reports</p>
                        </div>
                    </div>
                </a>

                <a href="{{ url('/equipment') }}" class="block bg-white/20 hover:bg-white/30 transition rounded-lg p-4 border border-white/30 backdrop-blur-sm">
                    <div class="flex items-center">
                        <div class="bg-white/30 rounded-lg p-3 mr-3">
                            <i class="fas fa-server text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Review Inventory</p>
                            <p class="text-sm opacity-90">Check equipment status</p>
                        </div>
                    </div>
                </a>

                <a href="{{ url('/software') }}" class="block bg-white/20 hover:bg-white/30 transition rounded-lg p-4 border border-white/30 backdrop-blur-sm">
                    <div class="flex items-center">
                        <div class="bg-white/30 rounded-lg p-3 mr-3">
                            <i class="fas fa-cube text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Manage Software</p>
                            <p class="text-sm opacity-90">Update software inventory</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

{{-- ======================================================================== --}}
{{-- ROLE 5: LECTURER — Own bookings, requests, reports only                  --}}
{{-- ======================================================================== --}}
@elseif ($role === 5)

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- My Upcoming Bookings -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border-l-4 border-green-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">My Upcoming Bookings</p>
                        <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $counts['my_upcoming_bookings'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-green-100 rounded-full p-4">
                        <i class="fas fa-calendar-check text-green-600 text-2xl"></i>
                    </div>
                </div>
                <a href="{{ url('/schedules') }}" class="mt-4 inline-flex items-center text-green-600 hover:text-green-700 font-medium text-sm">
                    View Schedule <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- My Pending Requests -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border-l-4 border-amber-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">My Pending Requests</p>
                        <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $counts['my_pending_requests'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-amber-100 rounded-full p-4">
                        <i class="fas fa-clock text-amber-600 text-2xl"></i>
                    </div>
                </div>
                <a href="{{ url('/booking-requests') }}" class="mt-4 inline-flex items-center text-amber-600 hover:text-amber-700 font-medium text-sm">
                    View Requests <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- My Software Requests -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border-l-4 border-orange-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">My Software Requests</p>
                        <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $counts['my_software_requests'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-orange-100 rounded-full p-4">
                        <i class="fas fa-cube text-orange-600 text-2xl"></i>
                    </div>
                </div>
                <a href="{{ url('/software-requests/create') }}" class="mt-4 inline-flex items-center text-orange-600 hover:text-orange-700 font-medium text-sm">
                    View Requests <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- My Fault Reports -->
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border-l-4 border-red-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">My Fault Reports</p>
                        <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $counts['my_fault_reports'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-red-100 rounded-full p-4">
                        <i class="fas fa-flag text-red-600 text-2xl"></i>
                    </div>
                </div>
                <a href="{{ url('/reports') }}" class="mt-4 inline-flex items-center text-red-600 hover:text-red-700 font-medium text-sm">
                    View Reports <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- My Recent Activity -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center mb-6">
                <i class="fas fa-history text-blue-600 text-xl mr-3"></i>
                <h3 class="text-xl font-bold text-slate-900">My Recent Activity</h3>
            </div>

            <p class="text-slate-600 mb-6">
                Track your lab bookings, booking requests, and submitted fault reports. All data shown is scoped to your account only.
            </p>

            <!-- Recent Bookings -->
            <div class="mb-6">
                <h4 class="font-semibold text-slate-700 mb-3">
                    <i class="fas fa-calendar-check mr-2 text-green-600"></i>My Recent Bookings
                </h4>
                @if (!empty($myRecentBookings) && count($myRecentBookings))
                    <div class="space-y-2">
                        @foreach ($myRecentBookings as $booking)
                            @php
                                $bookingBadgeClass = match ((int) $booking->status) {
                                    2 => 'bg-green-100 text-green-800',
                                    3 => 'bg-red-100 text-red-800',
                                    default => 'bg-amber-100 text-amber-800',
                                };
                                $bookingStatusText = match ((int) $booking->status) {
                                    2 => 'Approved',
                                    3 => 'Rejected',
                                    default => 'Pending',
                                };
                            @endphp
                            <div class="flex items-center justify-between bg-slate-50 rounded-lg p-3 border border-slate-200">
                                <div>
                                    <span class="font-medium text-slate-800">{{ $booking->laboratory->lab_name ?? 'Lab #' . $booking->lab_id }}</span>
                                    <br>
                                    <span class="text-xs text-slate-400">
                                        {{ \Carbon\Carbon::parse($booking->date)->format('d M Y') }}
                                        | {{ substr($booking->start_time, 0, 5) }} – {{ substr($booking->end_time, 0, 5) }}
                                    </span>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $bookingBadgeClass }}">{{ $bookingStatusText }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-400 text-sm italic">No bookings yet. Submit a booking request to get started.</p>
                @endif
            </div>

            <!-- Recent Fault Reports -->
            <div>
                <h4 class="font-semibold text-slate-700 mb-3">
                    <i class="fas fa-flag mr-2 text-red-600"></i>My Recent Fault Reports
                </h4>
                @if (!empty($myRecentReports) && count($myRecentReports))
                    <div class="space-y-2">
                        @foreach ($myRecentReports as $report)
                            @php
                                $statusClass = match ((int) $report->status) {
                                    2 => 'bg-amber-100 text-amber-800',
                                    3 => 'bg-green-100 text-green-800',
                                    default => 'bg-red-100 text-red-800',
                                };
                                $statusLabel = match ((int) $report->status) {
                                    2 => 'In Progress',
                                    3 => 'Resolved',
                                    default => 'Open',
                                };
                            @endphp
                            <div class="flex items-center justify-between bg-slate-50 rounded-lg p-3 border border-slate-200">
                                <div>
                                    <span class="font-medium text-slate-800">{{ $report->issues_type }}</span>
                                    <span class="text-slate-500 text-sm ml-2">— {{ Str::limit($report->description, 60) }}</span>
                                    <br>
                                    <span class="text-xs text-slate-400">
                                        {{ \Carbon\Carbon::parse($report->reported_date)->format('d M Y') }}
                                    </span>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-400 text-sm italic">No fault reports submitted yet.</p>
                @endif
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="bg-gradient-to-br from-teal-600 to-cyan-700 rounded-lg shadow-md p-6 text-white">
            <div class="flex items-center mb-6">
                <i class="fas fa-lightning-bolt text-yellow-200 text-xl mr-3"></i>
                <h3 class="text-xl font-bold">Quick Actions</h3>
            </div>

            <div class="space-y-3">
                <a href="{{ url('/booking-requests/create') }}" class="block bg-white/20 hover:bg-white/30 transition rounded-lg p-4 border border-white/30 backdrop-blur-sm">
                    <div class="flex items-center">
                        <div class="bg-white/30 rounded-lg p-3 mr-3">
                            <i class="fas fa-calendar-plus text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="font-semibold">New Booking Request</p>
                            <p class="text-sm opacity-90">Request a lab booking</p>
                        </div>
                    </div>
                </a>

                <a href="{{ url('/reports/add') }}" class="block bg-white/20 hover:bg-white/30 transition rounded-lg p-4 border border-white/30 backdrop-blur-sm">
                    <div class="flex items-center">
                        <div class="bg-white/30 rounded-lg p-3 mr-3">
                            <i class="fas fa-flag text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Submit Fault Report</p>
                            <p class="text-sm opacity-90">Report a lab issue</p>
                        </div>
                    </div>
                </a>

                <a href="{{ url('/booking-requests') }}" class="block bg-white/20 hover:bg-white/30 transition rounded-lg p-4 border border-white/30 backdrop-blur-sm">
                    <div class="flex items-center">
                        <div class="bg-white/30 rounded-lg p-3 mr-3">
                            <i class="fas fa-list-check text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="font-semibold">View My Booking Requests</p>
                            <p class="text-sm opacity-90">Track your request status</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

@endif

@endsection