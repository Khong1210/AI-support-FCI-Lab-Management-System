<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AI Lab Management') }} | @yield('title', 'Dashboard')</title>

    <!-- Local CSS Files -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    <!-- Font Awesome from CDN (for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Additional styling */
        .navbar-white .nav-link:hover { color: #007bff !important; }
        .brand-link { font-size: 1.25rem; font-weight: 600; }
        .content-header h1 { font-weight: 700; color: #333; }
    </style>
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ url('/admin') }}" class="brand-link text-center">
            <i class="fas fa-flask mr-2"></i>
            <span>AI Lab Admin</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="{{ url('/admin') }}" class="nav-link {{ request()->is('admin') && !request()->is('admin/*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <li class="nav-header">MANAGEMENT</li>

                    <!-- Users -->
                    <li class="nav-item">
                        <a href="{{ url('/admin/users') }}" class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Users</p>
                        </a>
                    </li>

                    <!-- Equipment -->
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-desktop"></i>
                            <p>Equipment</p>
                        </a>
                    </li>

                    <!-- Software -->
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-cube"></i>
                            <p>Software</p>
                        </a>
                    </li>

                    <!-- Laboratories -->
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-flask-vial"></i>
                            <p>Laboratories</p>
                        </a>
                    </li>

                    <!-- Courses -->
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-book"></i>
                            <p>Courses</p>
                        </a>
                    </li>

                    <!-- Bookings -->
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-calendar-alt"></i>
                            <p>Bookings</p>
                        </a>
                    </li>

                    <li class="nav-header">REPORTS & ANALYTICS</li>

                    <!-- Reports -->
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-file-pdf"></i>
                            <p>Reports</p>
                        </a>
                    </li>

                    <!-- Schedules -->
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-clock"></i>
                            <p>Schedules</p>
                        </a>
                    </li>

                    <!-- Software Requests -->
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-inbox"></i>
                            <p>Software Requests</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('page-title', 'Dashboard')</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ url('/admin') }}"><i class="fas fa-home"></i> Home</a></li>
                            <li class="breadcrumb-item active">@yield('breadcrumb', 'Dashboard')</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>
                        <strong>Success!</strong> {{ session('status') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <strong>Error!</strong> Please fix the errors below.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @yield('content')
            </div>
        </section>
    </div>

    <!-- Footer -->
    {{-- <footer class="main-footer">
        <div class="float-right d-none d-sm-block">
            <b>Version</b> 1.0.0
        </div>
        <strong>&copy; 2026 AI Support FCI Lab Management System.</strong> All rights reserved.
    </footer> --}}
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.bundle.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    // Sidebar toggle
    $(document).ready(function() {
        $('[data-widget="pushmenu"]').on('click', function(e) {
            e.preventDefault();
            $('body').toggleClass('sidebar-collapse');
        });
        
        // Dropdown functionality
        $('.dropdown-toggle').on('click', function(e) {
            e.preventDefault();
            $(this).next('.dropdown-menu').toggle();
        });
        
        // Close dropdowns when clicking elsewhere
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown').length) {
                $('.dropdown-menu').hide();
            }
        });
    });
</script>
@stack('scripts')
</body>
</html>

