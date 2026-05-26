<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Report;

class MailController extends Controller
{
    public function index()
    {
        return view('admin.mail.index', [
            'pendingBookings' => Booking::with(['user', 'laboratory'])
                ->where('status', 1)
                ->orderBy('date')
                ->orderBy('start_time')
                ->get(),
            'openReports' => Report::with(['user', 'laboratory'])
                ->whereIn('status', [1, 2])
                ->orderByDesc('reported_date')
                ->get(),
        ]);
    }
}
