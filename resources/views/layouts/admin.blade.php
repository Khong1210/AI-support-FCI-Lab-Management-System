<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'AI Lab Management') }} | @yield('title', 'Dashboard')</title>

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary: #1e40af;
            --primary-dark: #1e3a8a;
            --secondary: #0891b2;
            --success: #059669;
            --warning: #d97706;
            --danger: #dc2626;
            --info: #0284c7;
            --light: #f8fafc;
            --dark: #1f2937;
        }
        * { box-sizing: border-box; }
        html, body { 
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50">
<div class="flex h-screen bg-slate-100">
    <!-- Sidebar -->
    <div class="hidden md:flex flex-col w-64 bg-slate-900 text-white shadow-lg">
        <!-- Logo -->
        <div class="flex items-center justify-center h-16 bg-slate-800 border-b border-slate-700">
            <a href="{{ url('/admin') }}" class="flex items-center space-x-2 font-bold text-lg">
                <i class="fas fa-flask text-blue-400"></i>
                <span>AI Lab Admin</span>
            </a>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto py-4">
            <div class="px-4">
                <!-- Dashboard -->
                <a href="{{ url('/admin') }}" class="flex items-center px-4 py-3 rounded-lg mb-2 transition {{ request()->is('admin') && !request()->is('admin/*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span class="ml-3">Dashboard</span>
                </a>

                <!-- Management Section -->
                <div class="mt-6">
                    <h3 class="px-4 py-2 text-xs font-semibold text-slate-400 uppercase">Management</h3>
                    
                    <a href="{{ url('/admin/users') }}" class="flex items-center px-4 py-3 rounded-lg mb-2 transition {{ request()->is('admin/users*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                        <i class="fas fa-users w-5"></i>
                        <span class="ml-3">Users</span>
                    </a>

                    <a href="{{ url('/admin/equipment') }}" class="flex items-center px-4 py-3 rounded-lg mb-2 transition {{ request()->is('admin/equipment*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                        <i class="fas fa-desktop w-5"></i>
                        <span class="ml-3">Equipment</span>
                    </a>

                    <a href="{{ url('/admin/software') }}" class="flex items-center px-4 py-3 rounded-lg mb-2 transition {{ request()->is('admin/software*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                        <i class="fas fa-cube w-5"></i>
                        <span class="ml-3">Software</span>
                    </a>

                    <a href="{{ url('/admin/laboratories') }}" class="flex items-center px-4 py-3 rounded-lg mb-2 transition {{ request()->is('admin/laboratories*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                        <i class="fas fa-flask-vial w-5"></i>
                        <span class="ml-3">Laboratories</span>
                    </a>

                    <a href="{{ url('/admin/courses') }}" class="flex items-center px-4 py-3 rounded-lg mb-2 transition {{ request()->is('admin/courses*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                        <i class="fas fa-book w-5"></i>
                        <span class="ml-3">Courses</span>
                    </a>

                    <a href="{{ url('/admin/semesters') }}" class="flex items-center px-4 py-3 rounded-lg mb-2 transition {{ request()->is('admin/semesters*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                        <i class="fas fa-calendar-alt w-5"></i>
                        <span class="ml-3">Semesters</span>
                    </a>

                    <a href="#" class="flex items-center px-4 py-3 rounded-lg mb-2 transition text-slate-300 hover:bg-slate-800">
                        <i class="fas fa-calendar-check w-5"></i>
                        <span class="ml-3">Bookings</span>
                    </a>
                </div>

                <!-- Reports & Analytics Section -->
                <div class="mt-6">
                    <h3 class="px-4 py-2 text-xs font-semibold text-slate-400 uppercase">Reports & Analytics</h3>
                    
                    <a href="#" class="flex items-center px-4 py-3 rounded-lg mb-2 transition text-slate-300 hover:bg-slate-800">
                        <i class="fas fa-file-pdf w-5"></i>
                        <span class="ml-3">Reports</span>
                    </a>

                    <a href="{{ url('/admin/schedules') }}" class="flex items-center px-4 py-3 rounded-lg mb-2 transition {{ request()->is('admin/schedules*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                        <i class="fas fa-clock w-5"></i>
                        <span class="ml-3">Schedules</span>
                    </a>

                    <a href="#" class="flex items-center px-4 py-3 rounded-lg mb-2 transition text-slate-300 hover:bg-slate-800">
                        <i class="fas fa-inbox w-5"></i>
                        <span class="ml-3">Software Requests</span>
                    </a>
                </div>
            </div>
        </nav>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Navigation Bar -->
        <header class="bg-white border-b border-slate-200 shadow-sm">
            <div class="flex items-center justify-between h-16 px-6">
                <!-- Left Section -->
                <div class="flex items-center space-x-4">
                    <button class="md:hidden text-slate-600 hover:text-slate-900">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h2 class="text-slate-900 font-semibold">{{ config('app.name', 'AI Lab Management') }}</h2>
                </div>

                <!-- Right Section -->
                <div class="flex items-center space-x-6">
                    <a href="{{ url('/admin/semesters') }}" class="text-slate-600 hover:text-slate-900 text-sm font-medium flex items-center space-x-2">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Semesters</span>
                    </a>
                    
                    <div class="relative group">
                        <button class="text-slate-600 hover:text-slate-900 text-lg">
                            <i class="fas fa-user-circle"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg hidden group-hover:block z-50">
                            <a href="#" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 first:rounded-t-lg">Profile</a>
                            <a href="#" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 last:rounded-b-lg">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <div class="p-6">
                <!-- Breadcrumb -->
                <div class="mb-6">
                    <nav class="flex items-center space-x-2 text-sm">
                        <a href="{{ url('/admin') }}" class="text-slate-600 hover:text-slate-900 flex items-center">
                            <i class="fas fa-home mr-2"></i>Home
                        </a>
                        <span class="text-slate-400">/</span>
                        <span class="text-slate-900 font-medium">@yield('breadcrumb', 'Dashboard')</span>
                    </nav>
                </div>

                <!-- Page Title -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-slate-900">@yield('page-title', 'Dashboard')</h1>
                </div>

                <!-- Alerts -->
                @if (session('status'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start space-x-3">
                        <i class="fas fa-check-circle text-green-600 mt-1"></i>
                        <div>
                            <h3 class="font-semibold text-green-900">Success!</h3>
                            <p class="text-sm text-green-700">{{ session('status') }}</p>
                        </div>
                        <button onclick="this.parentElement.style.display='none'" class="ml-auto text-green-600 hover:text-green-900">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start space-x-3">
                        <i class="fas fa-exclamation-circle text-red-600 mt-1"></i>
                        <div>
                            <h3 class="font-semibold text-red-900">Error!</h3>
                            <p class="text-sm text-red-700">Please fix the errors below.</p>
                        </div>
                        <button onclick="this.parentElement.style.display='none'" class="ml-auto text-red-600 hover:text-red-900">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                <!-- Page Content -->
                @yield('content')
            </div>

            <!-- Footer -->
            <footer class="bg-slate-100 border-t border-slate-200 px-6 py-4 text-sm text-slate-600">
                <div class="flex items-center justify-between">
                    <div>
                        <strong class="text-slate-900">© 2026 AI Support FCI Lab Management System.</strong> All rights reserved.
                    </div>
                    <div class="text-slate-500">
                        <b>Version</b> 1.0.0
                    </div>
                </div>
            </footer>
        </main>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
@stack('scripts')
</body>
</html>

