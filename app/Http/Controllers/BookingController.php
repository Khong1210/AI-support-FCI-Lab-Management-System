<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Laboratory;
use App\Models\User;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected static array $statuses = [
        1 => 'Pending',
        2 => 'Accepted',
        3 => 'Rejected',
        4 => 'Cancelled',
    ];

    public function index(Request $request)
    {
        $query = Booking::with(['user', 'laboratory']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($labId = $request->input('lab_id')) {
            $query->where('lab_id', $labId);
        }

        return view('admin.bookings.index', [
            'bookings' => $query->orderByDesc('date')->orderBy('start_time')->get(),
            'statuses' => self::$statuses,
            'laboratories' => Laboratory::orderBy('lab_name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.bookings.create', [
            'users' => User::orderBy('username')->get(),
            'laboratories' => Laboratory::orderBy('lab_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'lab_id' => 'required|exists:laboratories,id',
            'purpose' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        Booking::create([
            'user_id' => $request->input('user_id'),
            'lab_id' => $request->input('lab_id'),
            'purpose' => $request->input('purpose'),
            'date' => $request->input('date'),
            'start_time' => $request->input('start_time') . ':00',
            'end_time' => $request->input('end_time') . ':00',
            'status' => 1,
        ]);

        return redirect('/admin/bookings')->with('status', 'Booking request created successfully.');
    }

    public function accept(Booking $booking)
    {
        $booking->update(['status' => 2]);

        return redirect('/admin/bookings')->with('status', 'Booking request accepted.');
    }

    public function reject(Booking $booking)
    {
        $booking->update(['status' => 3]);

        return redirect('/admin/bookings')->with('status', 'Booking request rejected.');
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();

        return redirect('/admin/bookings')->with('status', 'Booking request deleted.');
    }
}
