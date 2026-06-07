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
    
<style>
   
    .main-sidebar {
        width: 280px !important;
        background-color: #1e282c !important; 
        border-right: 1px solid #d2d6de;
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        z-index: 1030;
        overflow-x: hidden !important;
        transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    
    .content-wrapper, .main-footer {
        margin-left: 280px !important;
        transition: margin-left 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    
    .brand-link-container {
        background-color: #1a2226 !important;
        border-bottom: 1px solid #1e282c;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 18px;
        height: 56px;
        flex-shrink: 0;
    }
    .brand-link-text {
        color: #fff !important;
        font-weight: 600;
        font-size: 16px;
        text-decoration: none !important;
        white-space: nowrap;
        display: flex;
        align-items: center;
    }
    .sidebar-toggle-btn {
        color: #b8c7ce !important;
        cursor: pointer;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }
    .sidebar-toggle-btn:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: #fff !important;
    }

    .sidebar-menu-wrapper {
        padding: 10px 0;
        flex-grow: 1;
        overflow-y: auto; 
        margin-bottom: 60px; 
    }
    .nav-sidebar .nav-item {
        width: 100%;
        margin: 2px 0;
    }
    .nav-sidebar .nav-item .nav-link {
        color: #b8c7ce !important;
        padding: 12px 18px !important;
        font-size: 14px;
        display: flex;
        align-items: center;
        text-decoration: none !important;
        transition: background-color 0.15s ease, color 0.15s ease;
        white-space: nowrap;
        position: relative;
    }
    
    .nav-sidebar .nav-item .nav-link .nav-icon-box {
        width: 24px;
        display: flex;
        align-items: center;
        justify-content: flex-start; 
        margin-right: 12px;
        font-size: 16px;
        transition: margin 0.25s ease, justify-content 0.25s ease;
    }
    .nav-sidebar .nav-item .nav-link p {
        margin: 0 !important;
        transition: opacity 0.15s ease;
        opacity: 1;
    }
    .nav-sidebar .nav-item .nav-link:hover {
        background-color: #161c1e !important;
        color: #fff !important;
    }
    .nav-sidebar .nav-item .nav-link.active {
        background-color: #007bff !important;
        color: #fff !important;
    }
    .nav-sidebar .nav-item .nav-link.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background-color: #0056b3;
    }
    
    .custom-sidebar-divider {
        display: block !important;
        font-size: 11px !important;
        text-transform: uppercase;
        padding: 20px 18px 8px 18px !important;
        color: #4b646f !important;
        font-weight: bold;
        white-space: nowrap;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        height: 45px; 
        line-height: 17px;
    }
    
    .nav-header {
        font-size: 11px !important;
        text-transform: uppercase;
        padding: 18px 18px 6px 18px !important;
        color: #4b646f !important;
        font-weight: bold;
        white-space: nowrap;
        height: auto;
        display: block !important; 
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .main-header {
        background-color: #ffffff !important;
        border-bottom: 1px solid #d2d6de !important;
        height: 56px;
        position: sticky;
        top: 0;
        z-index: 1020;
    }

    .sidebar-footer-wrapper {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: #1a2226; 
        z-index: 10;
        display: flex;
        flex-direction: column;
        height: 60px;
        box-shadow: 0 -2px 5px rgba(0,0,0,0.1);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
   
    .footer-divider {
        height: 1px;
        background-color: #2c3b41; 
        width: 100%;
    }
    .footer-btns {
        display: flex;
        align-items: center;
        justify-content: space-around;
        height: 100%;
        padding: 0 10px;
    }
    .footer-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #b8c7ce !important;
        text-decoration: none !important;
        font-size: 14px;
        padding: 8px 15px;
        border-radius: 4px;
        transition: all 0.15s ease;
        gap: 8px;
    }
    .footer-btn:hover {
        background-color: rgba(255, 255, 255, 0.05);
        color: #fff !important;
    }
    .text-danger-hover:hover {
        color: #dc3545 !important; 
    }

  
    .sidebar-collapse .main-sidebar {
        width: 64px !important; 
    }
    .sidebar-collapse .content-wrapper,
    .sidebar-collapse .main-header,
    .sidebar-collapse .main-footer {
        margin-left: 40px !important; 
    }
    
    .sidebar-collapse .brand-link-text span,
    .sidebar-collapse .nav-sidebar p,
    .sidebar-collapse .brand-link-text,
    .sidebar-collapse .footer-btn span {
        opacity: 0 !important;
        display: none !important;
    }
    
    .sidebar-collapse .brand-link-container {
        padding: 0 !important;
        justify-content: center !important;
    }
    
    .sidebar-collapse .nav-sidebar .nav-item .nav-link .nav-icon-box {
        margin-right: 0 !important;
        width: 100% !important;
        justify-content: center !important;
    }
    
    .sidebar-collapse .custom-sidebar-divider {
        font-size: 0 !important;
        color: transparent !important;
        padding: 22px 15px !important; 
        height: 45px !important; 
        position: relative;
        display: block !important;
    }
    .sidebar-collapse .custom-sidebar-divider::after {
        content: '';
        position: absolute;
        left: 15px;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        height: 1px;
        background-color: #374850 !important; 
        display: block !important;
    }
    
    .sidebar-collapse .sidebar-footer-wrapper {
        height: 100px; 
    }
    .sidebar-collapse .sidebar-menu-wrapper {
        margin-bottom: 100px; 
    }
    .sidebar-collapse .footer-btns {
        flex-direction: column; 
        justify-content: center;
        gap: 12px;
        padding: 10px 0;
    }
    .sidebar-collapse .footer-btn {
        width: 38px;
        height: 38px;
        padding: 0;
        border-radius: 50%; 
        font-size: 16px;
    }
</style>
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    
    <aside class="main-sidebar">
        <div class="brand-link-container">
            <a href="{{ url('/admin') }}" class="brand-link-text">
                {{-- <i class="fas fa-flask mr-2 text-info"></i> --}}
                <span>FCI Lab Management System</span>
            </a>
            <div class="sidebar-toggle-btn" data-widget="pushmenu" title="Toggle Sidebar">
                <i class="fas fa-bars"></i>
            </div>
        </div>

        <div class="sidebar-menu-wrapper">
            <ul class="nav nav-sidebar flex-column">
                <li class="nav-item">
                    <a href="{{ url('/admin') }}" class="nav-link {{ request()->is('admin') && !request()->is('admin/*') ? 'active' : '' }}">
                        <div class="nav-icon-box"><i class="fas fa-tachometer-alt"></i></div>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="custom-sidebar-divider" data-title="Management">Management</li>
                <li class="nav-item">
                    <a href="{{ url('/admin/users') }}" class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                        <div class="nav-icon-box"><i class="fas fa-users"></i></div>
                        <p>Users</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/equipment') }}" class="nav-link {{ request()->is('admin/equipment*') ? 'active' : '' }}">
                        <div class="nav-icon-box"><i class="fas fa-desktop"></i></div>
                        <p>Equipment</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/software') }}" class="nav-link {{ request()->is('admin/software*') ? 'active' : '' }}">
                        <div class="nav-icon-box"><i class="fas fa-cube"></i></div>
                        <p>Software</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/laboratories') }}" class="nav-link {{ request()->is('admin/laboratories*') ? 'active' : '' }}">
                        <div class="nav-icon-box"><i class="fas fa-flask-vial"></i></div>
                        <p>Laboratories</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/courses') }}" class="nav-link {{ request()->is('admin/courses*') ? 'active' : '' }}">
                        <div class="nav-icon-box"><i class="fas fa-book"></i></div>
                        <p>Courses</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/semesters') }}" class="nav-link {{ request()->is('admin/semesters*') ? 'active' : '' }}">
                        <div class="nav-icon-box"><i class="fas fa-calendar-alt"></i></div>
                        <p>Semesters</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/bookings') }}" class="nav-link {{ request()->is('admin/bookings*') ? 'active' : '' }}">
                        <div class="nav-icon-box"><i class="fas fa-calendar-check"></i></div>
                        <p>Bookings</p>
                    </a>
                </li>

                <li class="custom-sidebar-divider" data-title="Reports & Analytics">Reports & Analytics</li>
                <li class="nav-item">
                    <a href="{{ url('/admin/reports') }}" class="nav-link {{ request()->is('admin/reports*') ? 'active' : '' }}">
                        <div class="nav-icon-box"><i class="fas fa-file-pdf"></i></div>
                        <p>Reports</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/schedules') }}" class="nav-link {{ request()->is('admin/schedules*') ? 'active' : '' }}">
                        <div class="nav-icon-box"><i class="fas fa-clock"></i></div>
                        <p>Schedules</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/mail') }}" class="nav-link {{ request()->is('admin/mail*') ? 'active' : '' }}">
                        <div class="nav-icon-box"><i class="fas fa-inbox"></i></div>
                        <p>Mail / Inbox</p>
                    </a>
                </li>
            </ul>
        </div>
            <div class="sidebar-footer-wrapper">
            <div class="footer-divider"></div>
            
            <div class="footer-btns">
                <a href="#" class="footer-btn" title="Settings">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                
                <a href="#" class="footer-btn text-danger-hover" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

    </aside>

    <div class="content-wrapper">
        <header class="main-header flex items-center justify-between px-4">
            <nav class="flex items-center justify-between w-full">
                <ul class="flex items-center space-x-4">
                    <li>
                        <a href="{{ url('/admin') }}" class="text-gray-600 hover:text-blue-600 font-medium flex items-center">
                            <i class="fas fa-home mr-1"></i> Home
                        </a>
                    </li>
                </ul>
                <ul class="flex items-center space-x-4 ml-auto">
                    <li>
                        <a href="{{ url('/admin/semesters') }}" class="text-gray-600 hover:text-blue-600 flex items-center">
                            <i class="fas fa-calendar-alt mr-1"></i> Semesters
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/admin/mail') }}" class="text-gray-600 hover:text-blue-600 flex items-center">
                            <i class="fas fa-inbox mr-1"></i> Mail
                        </a>
                    </li>
                </ul>
            </nav>
        </header>

        <section class="content-header pt-4 px-4">
            <div class="container-fluid">
                <h1 class="text-2xl font-bold text-gray-800">@yield('page-title', 'Dashboard')</h1>
                <ol class="breadcrumb flex space-x-2 text-sm text-gray-500 mt-1">
                    <li class="breadcrumb-item"><a href="{{ url('/admin') }}" class="text-blue-600 hover:underline">Home</a></li>
                    <li class="breadcrumb-item text-gray-400">/</li>
                    <li class="breadcrumb-item active">@yield('breadcrumb', 'Dashboard')</li>
                </ol>
            </div>
        </section>

        <section class="content mt-3 px-4 pb-12">
            <div class="container-fluid">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible flex items-center p-3 mb-4 bg-green-100 text-green-800 border-l-4 border-green-600 rounded">
                        <i class="fas fa-check-circle mr-2"></i>
                        <div class="flex-1">{{ session('status') }}</div>
                        <button type="button" class="text-lg font-bold ml-2" onclick="this.parentElement.style.display='none'">&times;</button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible flex items-center p-3 mb-4 bg-red-100 text-red-800 border-l-4 border-red-600 rounded">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <div class="flex-1">Please fix the errors below.</div>
                        <button type="button" class="text-lg font-bold ml-2" onclick="this.parentElement.style.display='none'">&times;</button>
                    </div>
                @endif

                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer bg-white border-t border-gray-200 p-3 text-sm text-gray-600">
        <strong>&copy; 2026 AI Support FCI Lab Management System.</strong> All rights reserved.
        <span class="float-right text-gray-400"><b>Version</b> 1.0.0</span>
    </footer>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // SweetAlert2 防呆
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
              title: "Are you sure?",
              text: "You won't be able to revert this!",
              icon: "warning",
              showCancelButton: true,
              confirmButtonColor: "#3085d6",
              cancelButtonColor: "#d33",
              confirmButtonText: "Yes, delete it!"
            }).then((result) => {
              if (result.isConfirmed) {
                Swal.fire({
                  title: "Deleted!",
                  text: "Your record is being deleted.",
                  icon: "success",
                  showConfirmButton: false,
                  timer: 1000
                });
                setTimeout(() => form.submit(), 1000);
              }
            });
        });
    });

    // 🌟 全域單擊事件：控制絲滑折疊切換
    document.addEventListener('click', function(e) {
        const pushmenu = e.target.closest('[data-widget="pushmenu"]');
        if (pushmenu) {
            e.preventDefault();
            document.body.classList.toggle('sidebar-collapse');
        }
    });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 找到你的側邊欄切換漢堡按鈕 (請根據你實際的 class 名稱調整，例如 .sidebar-toggle-btn)
    const toggleBtn = document.querySelector('.sidebar-toggle-btn');
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            // 給一點小延遲，確保 class 已經切換完畢
            setTimeout(() => {
                // 檢查現在 body 是否含有縮小的 class
                if (document.body.classList.contains('sidebar-collapse') || document.documentElement.classList.contains('sidebar-collapse')) {
                    localStorage.setItem('sidebar-state', 'collapsed'); // 記住縮小了
                } else {
                    localStorage.setItem('sidebar-state', 'expanded');  // 記住展開了
                }
            }, 100);
        });
    }
});
</script>
@stack('scripts')
</body>
</html>