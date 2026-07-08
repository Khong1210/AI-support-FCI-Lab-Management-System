<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingRequest;
use App\Models\Course;
use App\Models\Equipment;
use App\Models\Laboratory;
use App\Models\Report;
use App\Models\Schedule;
use App\Models\Software;
use App\Models\SoftwareRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    protected static array $roles = [
        1 => 'Admin',
        2 => 'System Manager',
        3 => 'Lab Staff',
        4 => 'Lab Committee',
        5 => 'Lecturer',
    ];

    public function dashboard()
    {
        $user = auth()->user();
        $role = (int) $user->user_role;

        // ── Role 1 (Admin) — unchanged, full system overview ──
        if ($role === 1) {
            return view('admin.dashboard', [
                'counts' => [
                    'users'         => User::count(),
                    'equipment'     => Equipment::count(),
                    'software'      => Software::count(),
                    'laboratories'  => Laboratory::count(),
                    'courses'       => Course::count(),
                    'bookings'      => Booking::count(),
                    'reports'       => Report::count(),
                    'schedules'     => Schedule::count(),
                    'requests'      => SoftwareRequest::count(),
                ],
            ]);
        }

        // ── Role 2 (Faculty Manager) — approvals + academic oversight ──
        if ($role === 2) {
            return view('admin.dashboard', [
                'counts' => [
                    'pending_booking_requests'  => BookingRequest::where('status', 'pending')->count(),
                    'pending_software_requests' => SoftwareRequest::where('status', SoftwareRequest::STATUS_PENDING)->count(),
                    'active_courses'            => Course::count(),
                    'active_schedules'          => Schedule::count(),
                    'equipment'                 => Equipment::count(),
                    'software'                  => Software::count(),
                    'laboratories'              => Laboratory::count(),
                    'reports'                   => Report::count(),
                ],
                'pendingBookings'  => BookingRequest::with('user', 'laboratory')
                    ->where('status', 'pending')
                    ->latest()
                    ->take(5)
                    ->get(),
                'pendingSoftware'  => SoftwareRequest::with('user', 'software')
                    ->where('status', SoftwareRequest::STATUS_PENDING)
                    ->latest()
                    ->take(5)
                    ->get(),
            ]);
        }

        // ── Role 3 (Lab Staff) — fault reports + inventory ──
        if ($role === 3) {
            return view('admin.dashboard', [
                'counts' => [
                    'open_reports' => Report::where('status', 1)->count(),
                    'equipment'    => Equipment::count(),
                    'software'     => Software::count(),
                    'laboratories' => Laboratory::count(),
                ],
                'openReports' => Report::with('user', 'laboratory')
                    ->where('status', 1)
                    ->latest()
                    ->take(5)
                    ->get(),
            ]);
        }

        // ── Role 4 (Lab Committee) — oversight across fault reports, equipment, software, bookings ──
        if ($role === 4) {
            return view('admin.dashboard', [
                'counts' => [
                    'open_reports'       => Report::where('status', 1)->count(),
                    'in_progress_reports' => Report::where('status', 2)->count(),
                    'resolved_reports'   => Report::where('status', 3)->count(),
                    'equipment'          => Equipment::count(),
                    'software'           => Software::count(),
                    'active_bookings'    => Booking::count(),
                ],
                'openReports' => Report::with('user', 'laboratory')
                    ->where('status', 1)
                    ->latest()
                    ->take(5)
                    ->get(),
            ]);
        }

        // ── Role 5 (Lecturer) — own bookings, requests, reports only ──
        if ($role === 5) {
            $userId = $user->id;
            return view('admin.dashboard', [
                'counts' => [
                    'my_upcoming_bookings'     => Booking::where('user_id', $userId)
                        ->where('date', '>=', now()->toDateString())
                        ->count(),
                    'my_pending_requests'      => BookingRequest::where('user_id', $userId)
                        ->where('status', 'pending')
                        ->count(),
                    'my_software_requests'     => SoftwareRequest::where('user_id', $userId)->count(),
                    'my_fault_reports'         => Report::where('user_id', $userId)->count(),
                ],
                'myRecentBookings' => Booking::where('user_id', $userId)
                    ->with('laboratory')
                    ->latest()
                    ->take(5)
                    ->get(),
                'myRecentReports' => Report::where('user_id', $userId)
                    ->latest()
                    ->take(3)
                    ->get(),
            ]);
        }

        // Fallback — shouldn't happen but guard against unhandled roles
        return view('admin.dashboard', ['counts' => []]);
    }

   public function users(Request $request)
{
    $query = User::query();

    if ($search = $request->input('search')) {
        $query->where(function ($subQuery) use ($search) {
            $subQuery->where('username', 'like', "%{$search}%")
                     ->orWhere('email', 'like', "%{$search}%");
        });
    }

    if ($role = $request->input('role')) {
        $query->where('user_role', $role);
    }

    $sort = $request->input('sort', 'id');
    $order = $request->input('order', 'asc');
    
    $query->orderBy($sort, $order);

    return view('admin.users.index', [
        'users' => $query->get(),
        'roles' => self::$roles,
    ]);
}

    public function createUser()
    {
        return view('admin.users.create', ['roles' => self::$roles]);
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'user_role' => 'required|integer|in:1,2,3,4,5',
        ]);

        User::create([
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'user_role' => $request->input('user_role'),
        ]);

        return redirect('/management/users')->with('status', 'User created successfully.');
    }

    public function editUser(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => self::$roles,
        ]);
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'user_role' => 'required|integer|in:1,2,3,4,5',
        ]);

        $data = [
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'user_role' => $request->input('user_role'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $user->update($data);

        return redirect('/management/users')->with('status', 'User updated successfully.');
    }

    public function destroyUser(User $user)
    {
        $user->delete();

        return redirect('/management/users')->with('status', 'User deleted successfully.');
    }
}
