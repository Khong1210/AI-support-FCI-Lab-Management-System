<?php

namespace App\Http\Controllers;

use App\Models\Booking;
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
        return view('admin.dashboard', [
            'counts' => [
                'users' => User::count(),
                'equipment' => Equipment::count(),
                'software' => Software::count(),
                'laboratories' => Laboratory::count(),
                'courses' => Course::count(),
                'bookings' => Booking::count(),
                'reports' => Report::count(),
                'schedules' => Schedule::count(),
                'requests' => SoftwareRequest::count(),
            ],
        ]);
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

        return view('admin.users.index', [
            'users' => $query->orderBy('id')->get(),
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

        return redirect('/admin/users')->with('status', 'User created successfully.');
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

        return redirect('/admin/users')->with('status', 'User updated successfully.');
    }

    public function destroyUser(User $user)
    {
        $user->delete();

        return redirect('/admin/users')->with('status', 'User deleted successfully.');
    }
}
