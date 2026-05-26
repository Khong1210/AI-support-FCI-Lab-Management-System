<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'AI Lab Management') }} | @yield('title', 'Dashboard')</title>

    <link rel="stylesheet" href="{{ asset('bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body class="hold-transition">
<div class="wrapper">
    <aside class="main-sidebar">
        <a href="{{ url('/admin') }}" class="brand-link">
            <i class="fas fa-flask mr-2"></i>
            <span>AI Lab Admin</span>
        </a>

        <div class="sidebar">
            <ul class="nav nav-sidebar flex-column">
                <li class="nav-item">
                    <a href="{{ url('/admin') }}" class="nav-link {{ request()->is('admin') && !request()->is('admin/*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-header">Management</li>
                <li class="nav-item">
                    <a href="{{ url('/admin/users') }}" class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Users</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/equipment') }}" class="nav-link {{ request()->is('admin/equipment*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-desktop"></i>
                        <p>Equipment</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/software') }}" class="nav-link {{ request()->is('admin/software*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-cube"></i>
                        <p>Software</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/laboratories') }}" class="nav-link {{ request()->is('admin/laboratories*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-flask-vial"></i>
                        <p>Laboratories</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/courses') }}" class="nav-link {{ request()->is('admin/courses*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-book"></i>
                        <p>Courses</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/semesters') }}" class="nav-link {{ request()->is('admin/semesters*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p>Semesters</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/bookings') }}" class="nav-link {{ request()->is('admin/bookings*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-calendar-check"></i>
                        <p>Bookings</p>
                    </a>
                </li>

                <li class="nav-header">Reports & Analytics</li>
                <li class="nav-item">
                    <a href="{{ url('/admin/reports') }}" class="nav-link {{ request()->is('admin/reports*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-pdf"></i>
                        <p>Reports</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/schedules') }}" class="nav-link {{ request()->is('admin/schedules*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-clock"></i>
                        <p>Schedules</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/mail') }}" class="nav-link {{ request()->is('admin/mail*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-inbox"></i>
                        <p>Mail / Inbox</p>
                    </a>
                </li>
            </ul>
        </div>
    </aside>

    <div class="content-wrapper">
        <header class="main-header">
            <nav class="navbar-expand">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a href="{{ url('/admin') }}" class="nav-link">
                            <i class="fas fa-home"></i>
                            Home
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a href="{{ url('/admin/semesters') }}" class="nav-link">
                            <i class="fas fa-calendar-alt"></i>
                            Semesters
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/admin/mail') }}" class="nav-link">
                            <i class="fas fa-inbox"></i>
                            Mail
                        </a>
                    </li>
                </ul>
            </nav>
        </header>

        <section class="content-header">
            <div class="container-fluid">
                <h1>@yield('page-title', 'Dashboard')</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/admin') }}">Home</a>
                    </li>
                    <li class="breadcrumb-item active">@yield('breadcrumb', 'Dashboard')</li>
                </ol>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" onclick="this.parentElement.style.display='none'">&times;</button>
                        <i class="fas fa-check-circle mr-1"></i>
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" onclick="this.parentElement.style.display='none'">&times;</button>
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        Please fix the errors below.
                    </div>
                @endif

                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>&copy; 2026 AI Support FCI Lab Management System.</strong> All rights reserved.
        <span class="float-right"><b>Version</b> 1.0.0</span>
    </footer>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
@stack('scripts')
</body>
</html>
