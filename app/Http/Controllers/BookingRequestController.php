<?php

namespace App\Http\Controllers;

use App\Models\BookingRequest;
use App\Models\Booking;
use App\Models\Laboratory;
use App\Models\Schedule;
use App\Models\Semester;
use App\Models\SystemMail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingRequestController extends Controller
{
    // -------------------------------------------------------------------------
    // PUBLIC — Booking Request Submission Form
    // -------------------------------------------------------------------------

    /**
     * Show the public booking request form.
     * Accessible at GET /booking-request
     */
    public function create()
    {
        $laboratories = Laboratory::where('status', 1)   // only active labs
                            ->orderBy('lab_name')
                            ->get();

        return view('booking-request.create', compact('laboratories'));
    }

    /**
     * Store a new booking request submitted by the public form.
     * Accessible at POST /booking-request
     */
    public function store(Request $request)
    {
        $request->validate([
            'lab_id'          => 'required|exists:laboratories,id',
            'date'            => 'required|date|after_or_equal:today',
            'start_time'      => ['required', 'regex:/^(0[8-9]|1[0-7]):00$/'],
            'end_time'        => ['required', 'regex:/^(0[9-9]|1[0-8]):00$/', 'after:start_time'],
            'reason'          => 'required|string|max:1000',
        ]);

        BookingRequest::create([
            'user_id'         => 1, // TODO: Replace with auth()->id() once Auth System is ready
            'lab_id'          => $request->input('lab_id'),
            'date'            => $request->input('date'),
            'start_time'      => $request->input('start_time') . ':00',
            'end_time'        => $request->input('end_time') . ':00',
            'reason'          => $request->input('reason'),
            'status'          => 'pending',
        ]);

        return redirect('/booking-request')
            ->with('success', 'Your booking request has been submitted successfully! The lab administrator will review it shortly.');
    }

    // -------------------------------------------------------------------------
    // PUBLIC AJAX — Availability Check
    // -------------------------------------------------------------------------

    /**
     * AJAX endpoint: Return all occupied time blocks for a given lab + date.
     * Checks the `schedules` table for:
     *   a) Single-date bookings (date matches exactly).
     *   b) Recurring schedules (is_recurring=1, day_of_week matches, and date falls within the semester).
     *
     * GET /api/booking-requests/availability?lab_id=X&date=Y
     *
     * Response JSON:
     * [
     *   { "start": "08:00", "end": "10:00" },
     *   { "start": "13:00", "end": "14:00" }
     * ]
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'lab_id' => 'required|exists:laboratories,id',
            'date'   => 'required|date',
        ]);

        $labId     = $request->input('lab_id');
        $date      = $request->input('date');
        $dayOfWeek = Carbon::parse($date)->format('l'); // e.g. "Monday"

        // Find the semester that covers this date
        $semester = Semester::whereDate('start_date', '<=', $date)
                            ->whereDate('end_date', '>=', $date)
                            ->first();
        $semesterId = $semester?->id;

        // Blocked by Schedules (regular classes + admin-created bookings/maintenance)
        $scheduleSlots = Schedule::where('lab_id', $labId)
            ->where(function ($q) use ($dayOfWeek, $date, $semesterId) {
                // a) Single-date event: matches exact date
                $q->where(function ($sub) use ($date) {
                    $sub->where('is_recurring', false)
                        ->where('date', $date);
                })
                // b) Recurring class: matches day of week AND falls within a valid semester
                ->orWhere(function ($sub) use ($dayOfWeek, $semesterId) {
                    $sub->where('is_recurring', true)
                        ->where('day_of_week', $dayOfWeek);
                    if ($semesterId) {
                        $sub->where('semester_id', $semesterId);
                    } else {
                        // If there's no semester covering the requested date,
                        // recurring schedules shouldn't technically block it, but
                        // we can optionally just match by day_of_week as a fallback.
                        // We will require a valid semester match if the schedule has one.
                    }
                });
            })
            ->get(['start_time', 'end_time']);

        // Merge and format into a unified array
        $occupied = $scheduleSlots->map(function ($item) {
            return [
                'start' => substr($item->start_time, 0, 5),
                'end'   => substr($item->end_time, 0, 5),
            ];
        })->values();

        return response()->json($occupied);
    }

    // -------------------------------------------------------------------------
    // ADMIN — List Booking Requests
    // -------------------------------------------------------------------------

    /**
     * Show the admin list of all booking requests.
     * Accessible at GET /admin/booking-requests
     */
    public function index(Request $request)
    {
        $query = BookingRequest::with(['laboratory', 'user']);

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by lab
        if ($labId = $request->input('lab_id')) {
            $query->where('lab_id', $labId);
        }

        $bookingRequests = $query->orderByDesc('date')->orderBy('start_time')->get();
        $laboratories    = Laboratory::orderBy('lab_name')->get();

        return view('admin.booking-requests.index', compact('bookingRequests', 'laboratories'));
    }

    // -------------------------------------------------------------------------
    // ADMIN — Approve Action
    // -------------------------------------------------------------------------

    /**
     * Approve a booking request.
     *
     * On approval:
     *  1. Find which semester covers the booking date (for semester_id in schedules).
     *  2. Create a record in `bookings` (approved, type='booking').
     *  3. Create a record in `schedules` (schedule_type='booking', linked to above booking).
     *  4. Mark the booking request as 'approved'.
     *
     * PUT /admin/booking-requests/{id}/approve
     */
    public function approve(int $id)
    {
        $bookingRequest = BookingRequest::findOrFail($id);

        if ($bookingRequest->status !== 'pending') {
            return redirect('/admin/booking-requests')
                ->with('error', 'This request has already been processed.');
        }

        DB::beginTransaction();
        try {
            $date = $bookingRequest->date;

            // ── Semester Matching ─────────────────────────────────────────────
            // Find the semester whose date range covers the booking date.
            // If no match is found, save semester_id = null
            $semester = Semester::whereDate('start_date', '<=', $date)
                                ->whereDate('end_date', '>=', $date)
                                ->first();
            $semesterId = $semester?->id;

            // ── Create Booking record ─────────────────────────────────────────
            // TODO: Replace hardcoded user_id with auth()->id() once Auth System is ready
            $userId = 1;

            $booking = Booking::create([
                'lab_id'      => $bookingRequest->lab_id,
                'type'        => 'booking',
                'user_id'     => $userId,
                'booker_name' => $bookingRequest->user->name ?? 'User',
                'purpose'     => $bookingRequest->reason,
                'date'        => $date,
                'start_time'  => $bookingRequest->start_time,
                'end_time'    => $bookingRequest->end_time,
                'status'      => 2, // 2 = Accepted (matches existing system convention)
            ]);

            // ── Create Schedule record ────────────────────────────────────────
            Schedule::create([
                'schedule_type' => 'booking',
                'semester_id'   => $semesterId,
                'lab_id'        => $bookingRequest->lab_id,
                'course_id'     => null,
                'booking_id'    => $booking->id,
                'date'          => $date,
                'day_of_week'   => Carbon::parse($date)->format('l'),
                'start_time'    => $bookingRequest->start_time,
                'end_time'      => $bookingRequest->end_time,
                'is_recurring'  => false,
            ]);

            // ── Update Request Status ─────────────────────────────────────────
            $bookingRequest->update(['status' => 'approved']);

            DB::commit();

            return redirect('/admin/booking-requests')
                ->with('status', "Booking request #{$bookingRequest->id} has been approved and scheduled successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ [BookingRequest] Failed to approve request #' . $id . ': ' . $e->getMessage());

            return redirect('/admin/booking-requests')
                ->with('error', 'Failed to approve request: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // ADMIN — Reject Action
    // -------------------------------------------------------------------------

    /**
     * Reject a booking request and generate a record in the system_mails table.
     *
     * Rejection flow:
     *  1. Mark the booking request as 'rejected'.
     *  2. Generate a record in `system_mails`.
     *
     * PUT /admin/booking-requests/{id}/reject
     */
    public function reject(Request $request, int $id)
    {
        $bookingRequest = BookingRequest::with('laboratory')->findOrFail($id);

        if ($bookingRequest->status !== 'pending') {
            return redirect('/admin/booking-requests')
                ->with('error', 'This request has already been processed.');
        }

        $rejectionReason = $request->input('rejection_reason', 'The requested time slot is not available.');

        // Update status
        $bookingRequest->update([
            'status'           => 'rejected',
            'rejection_reason' => $rejectionReason,
        ]);

        // ── Generate System Mail ───────────────────────────────────────────
        // TODO: Replace with auth()->id() or the actual requester's ID once Auth System is ready
        $userId = 1; // Since Auth is pending, target user_id = 1 for testing

        $labName = $bookingRequest->laboratory->lab_name ?? "Lab #{$bookingRequest->lab_id}";
        $date = Carbon::parse($bookingRequest->date)->format('l, d F Y');
        $time = substr($bookingRequest->start_time, 0, 5) . ' – ' . substr($bookingRequest->end_time, 0, 5);

        $body = "Dear User,\n\nWe regret to inform you that your lab booking request has been rejected by the administrator.\n\n";
        $body .= "Laboratory: {$labName}\n";
        $body .= "Date: {$date}\n";
        $body .= "Time: {$time}\n";
        $body .= "Your Reason: {$bookingRequest->reason}\n\n";
        if ($rejectionReason) {
            $body .= "Reason for Rejection: {$rejectionReason}\n\n";
        }
        $body .= "If you believe this is an error or you would like to submit a new request for a different time slot, please visit our booking portal or contact the lab administrator directly.\n\nThank you for your understanding.\n\nBest regards,\nFCI Lab Management Team";

        SystemMail::create([
            'user_id' => $userId,
            'subject' => '[FCI Lab] Your Lab Booking Request Has Been Rejected',
            'body'    => $body,
        ]);

        return redirect('/admin/booking-requests')
            ->with('status', "Booking request #{$bookingRequest->id} has been rejected. Notification recorded in system_mails.");
    }
}
