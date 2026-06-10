<?php

namespace App\Http\Controllers;

use App\Mail\BookingRejectedMail;
use App\Models\BookingRequest;
use App\Models\Booking;
use App\Models\Laboratory;
use App\Models\Schedule;
use App\Models\Semester;
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
            'requester_name'  => 'required|string|max:255',
            'requester_email' => 'required|email|max:255',
            'lab_id'          => 'required|exists:laboratories,id',
            'date'            => 'required|date|after_or_equal:today',
            'start_time'      => ['required', 'regex:/^(0[8-9]|1[0-7]):00$/'],
            'end_time'        => ['required', 'regex:/^(0[9-9]|1[0-8]):00$/', 'after:start_time'],
            'reason'          => 'required|string|max:1000',
        ]);

        BookingRequest::create([
            'lab_id'          => $request->input('lab_id'),
            'requester_name'  => $request->input('requester_name'),
            'requester_email' => $request->input('requester_email'),
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
     * Checks BOTH the `schedules` table (recurring classes + approved bookings)
     * AND the `bookings` table (approved ad-hoc bookings with status=2).
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

        // 1) Blocked by Schedules (regular classes + admin-created bookings/maintenance)
        $scheduleSlots = Schedule::where('lab_id', $labId)
            ->where(function ($q) use ($dayOfWeek, $date) {
                // Recurring class: matches day of week
                $q->where(function ($sub) use ($dayOfWeek) {
                    $sub->where('is_recurring', true)
                        ->where('day_of_week', $dayOfWeek);
                })
                // One-time event: matches exact date
                ->orWhere(function ($sub) use ($date) {
                    $sub->where('is_recurring', false)
                        ->where('date', $date);
                });
            })
            ->get(['start_time', 'end_time']);

        // 2) Blocked by approved Bookings (status=2 means approved in your system)
        $bookingSlots = Booking::where('lab_id', $labId)
            ->where('date', $date)
            ->where('status', 2)
            ->get(['start_time', 'end_time']);

        // 3) Merge and format both result sets into a unified array
        $occupied = $scheduleSlots->merge($bookingSlots)->map(function ($item) {
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
        $query = BookingRequest::with('laboratory');

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
            // If none found, semester_id is set to null (graceful degradation).
            $semester = Semester::whereDate('start_date', '<=', $date)
                                ->whereDate('end_date', '>=', $date)
                                ->first();
            $semesterId = $semester?->id; // null if no matching semester

            // ── Create Booking record ─────────────────────────────────────────
            // TODO: Replace hardcoded user_id with auth()->id() once Auth System is ready
            $booking = Booking::create([
                'lab_id'      => $bookingRequest->lab_id,
                'type'        => 'booking',
                'user_id'     => 1, // TODO: Replace with auth()->id() once Auth System is ready
                'booker_name' => $bookingRequest->requester_name,
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

            Log::info('✅ [BookingRequest] Approved request #' . $bookingRequest->id
                . ' | Lab: ' . ($bookingRequest->laboratory->lab_name ?? $bookingRequest->lab_id)
                . ' | Date: ' . $date
                . ' | Semester: ' . ($semester?->name ?? 'N/A (no matching semester)')
                . ' | Created Booking #' . $booking->id);

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
     * Reject a booking request and send a mock email notification.
     *
     * Rejection flow:
     *  1. Mark the booking request as 'rejected'.
     *  2. Instantiate BookingRejectedMail and call simulateSend() which writes
     *     the full email content to storage/logs/laravel.log.
     *
     * ── How to demonstrate this to your professor ──────────────────────────
     *  • Open storage/logs/laravel.log after clicking Reject.
     *  • You will see a structured log entry starting with "📧 [MOCK EMAIL]"
     *    containing the exact To address, subject, and body that would be sent.
     *  • To activate REAL email: configure MAIL_* in .env and replace the
     *    simulateSend() call with: Mail::to(...)->send(new BookingRejectedMail(...))
     * ──────────────────────────────────────────────────────────────────────
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

        // ── Mock Email Notification ───────────────────────────────────────────
        // TODO: Replace the simulateSend() call below with the following once SMTP is configured:
        //   Mail::to($bookingRequest->requester_email)->send(new BookingRejectedMail($bookingRequest));
        // Until then, simulateSend() logs the complete email content to storage/logs/laravel.log.
        $mail = new BookingRejectedMail($bookingRequest);
        $mail->simulateSend();

        return redirect('/admin/booking-requests')
            ->with('status', "Booking request #{$bookingRequest->id} has been rejected. Notification logged to laravel.log.");
    }
}
