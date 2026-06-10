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

        return view('admin.booking-requests.create', compact('laboratories'));
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

        return redirect('/admin/booking-requests')
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
    $dayOfWeek = \Carbon\Carbon::parse($date)->format('l'); // e.g. "Monday"

    // Find the semester that covers this date
    $semester = \App\Models\Semester::whereDate('start_date', '<=', $date)
                                ->whereDate('end_date', '>=', $date)
                                ->first();
    $semesterId = $semester?->id;

    // 1. Blocked by Schedules (regular classes + admin-created bookings/maintenance)
    $scheduleSlots = \App\Models\Schedule::where('lab_id', $labId)
        ->where(function ($q) use ($dayOfWeek, $date, $semesterId) {
            // 条件 a: 单次单天事件（必须匹配精确日期）
            $q->where(function ($sub) use ($date) {
                $sub->where('is_recurring', false)
                    ->whereDate('date', $date);
            })
            // 条件 b: 循环课（匹配星期几）
            ->orWhere(function ($sub) use ($dayOfWeek, $semesterId) {
                $sub->where('is_recurring', true)
                    ->where('day_of_week', $dayOfWeek);
                
                // 如果系统内能查到当前处于哪个学期，则同时校验学期匹配
                // 如果查不到或数据库没绑定，为了安全起见（防止漏掉课表），可以通过可选逻辑进行限制
                if ($semesterId) {
                    $sub->where(function($inner) use ($semesterId) {
                        $inner->where('semester_id', $semesterId)
                              ->orWhereNull('semester_id'); // 兼容没有写明学期的全局循环课
                    });
                }
            });
        })
        ->get(['start_time', 'end_time']);

    // 2. Blocked by Pending Booking Requests (方案A：还没决定的请求也直接视为占用)
    $pendingRequests = \App\Models\BookingRequest::where('lab_id', $labId)
        ->whereDate('date', $date)
        ->where('status', 'pending')
        ->get(['start_time', 'end_time']);

    // 3. 合并数据流并规范化格式
    $occupied = collect();

    foreach ($scheduleSlots as $item) {
        $occupied->push([
            'start' => substr($item->start_time, 0, 5),
            'end'   => substr($item->end_time, 0, 5),
        ]);
    }

    foreach ($pendingRequests as $item) {
        $occupied->push([
            'start' => substr($item->start_time, 0, 5),
            'end'   => substr($item->end_time, 0, 5),
        ]);
    }

    // 去重并重新排索引，打包成干净的 JSON 返回给前端
    return response()->json($occupied->unique()->values());
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

            // ── Generate System Mail ───────────────────────────────────────────
            $labName = $bookingRequest->laboratory->lab_name ?? "Lab #{$bookingRequest->lab_id}";
            $dateFormatted = Carbon::parse($date)->format('l, d F Y');
            $timeFormatted = substr($bookingRequest->start_time, 0, 5) . ' – ' . substr($bookingRequest->end_time, 0, 5);

            SystemMail::create([
                'user_id' => $userId,
                'subject' => '[FCI Lab] Your Lab Booking Request Has Been Approved',
                'body'    => "Your request for {$labName} on {$dateFormatted} ({$timeFormatted}) has been Approved.",
                'is_read' => false,
                'type'    => 'booking_status',
            ]);

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
        // 加上 try catch 捕获可能存在的数据库字段报错
        try {
            $bookingRequest = BookingRequest::with('laboratory')->findOrFail($id);

            // 关键修复：使用 strtolower，防止数据库里存的是 "Pending" 导致校验失败
            if (strtolower($bookingRequest->status) !== 'pending') {
                return redirect('/admin/booking-requests')
                    ->with('error', 'This request has already been processed (Current status: ' . $bookingRequest->status . ').');
            }

            $rejectionReason = $request->input('rejection_reason', 'The requested time slot is not available.');

            // Update status
            $bookingRequest->update([
                'status'           => 'rejected', // 建议保持跟数据库大小写一致，如果数据库用大写，这里改成 'Rejected'
                'rejection_reason' => $rejectionReason,
            ]);

            // ── Generate System Mail ───────────────────────────────────────────
            $userId = 1; 

            $labName = $bookingRequest->laboratory->lab_name ?? "Lab #{$bookingRequest->lab_id}";
            // 确保引入了 Carbon (可以用 \Carbon\Carbon)
            $date = \Carbon\Carbon::parse($bookingRequest->date)->format('l, d F Y');
            $time = substr($bookingRequest->start_time, 0, 5) . ' – ' . substr($bookingRequest->end_time, 0, 5);

            $body = "Dear User,\n\nWe regret to inform you that your lab booking request has been rejected by the administrator.\n\n";
            $body .= "Laboratory: {$labName}\n";
            $body .= "Date: {$date}\n";
            $body .= "Time: {$time}\n";
            $body .= "Your Reason: {$bookingRequest->reason}\n\n";
            if ($rejectionReason) {
                $body .= "Reason for Rejection: {$rejectionReason}\n\n";
            }
            $body .= "If you believe this is an error, please contact the lab administrator directly.\n\nBest regards,\nFCI Lab Management Team";

            SystemMail::create([
                'user_id' => $userId,
                'subject' => '[FCI Lab] Your Lab Booking Request Has Been Rejected',
                'body'    => $body,
                'is_read' => false,
                'type'    => 'booking_status',
            ]);

            return redirect('/admin/booking-requests')
                ->with('status', "Booking request #{$bookingRequest->id} has been rejected successfully.");

        } catch (\Exception $e) {
            // 如果中间有任何报错（比如 system_mails 表不存在，或者某个字段不合规），直接死在页面上让你看原因
            dd($e->getMessage());
        }
    }
}
