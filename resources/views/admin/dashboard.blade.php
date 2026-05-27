@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
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

    <!-- Recent Activity Section -->
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
@endsection
